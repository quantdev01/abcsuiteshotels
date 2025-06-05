<?php

set_time_limit(0);

// set to true if you are debugging with docker and pluglin as docker
$_ENV['IS_DOCKER'] = true;

/*
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder).
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf
 *            https://www.lineagrafica.es/licenses/license_es.pdf
 *            https://www.lineagrafica.es/licenses/license_fr.pdf
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'pluglin/vendor/autoload.php';

class Pluglin extends Module
{
    protected $config_form = false;
    public $path;
    private $js_url;
    private $api;

    /** Uncomment when PluglinDashboardController is working. Right now only used while developing.
        This piece of code will create a new entry in the "international" menu, pointing to:
            controllers/admin/PluglinDasboardController.php

        Only works on 1.7+

     public $tabs = [
        [
            'name' => 'Pluglin', // One name for all langs
            'class_name' => 'PluglinDashboard',
            'visible' => true,
            'parent_class_name' => 'AdminInternational',
        ],
    ];

     */

    /** @var string */
    private $js_path;

    /** @var string */
    private $css_path;

    /** @var string */
    private $img_path;

    /** @var string */
    private $docs_path;

    /** @var string */
    private $logo_path;

    /** @var string */
    private $module_path;

    /** @var array */
    private $content_types;
    private $url_panel;

    /** @var \Pluglin\Prestashop\Services\PluglinClient */
    private $pluglinClient;

    public function __construct()
    {
        $this->name = 'pluglin';
        $this->tab = 'i18n_localization';
        $this->version = '1.2.3';
        $this->author = 'Pluglin';
        $this->need_instance = 1;
        $this->module_key = 'b72c9165aa787be93828f4d7e7f1e737';
        $this->bootstrap = true; // compatible with backend bootstrap of 1.6+

        parent::__construct();

        $this->path = $this->_path;

        $this->displayName = $this->l('Pluglin - Translate your shop to every language');
        $this->description = $this->l(
            'Translate your shop into multiple languages automatically with high quality translations.'
        );

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];

        // Settings paths
        $this->js_path = $this->_path.'views/js/';
        $this->css_path = $this->_path.'views/css/';
        $this->img_path = $this->_path.'views/img/';
        $this->docs_path = $this->_path.'docs/';
        $this->logo_path = $this->_path.'logo.png';
        $this->module_path = $this->_path;

        $this->api = new PluglinClient();

        $this->registerHook('actionObjectAddAfter');

        $this->setContentTypes();

        $this->url_panel = 'https://app.pluglin.com/';
        $this->url_project = 'https://app.pluglin.com/projects/'.Configuration::get('PLUGLIN_PROJECT');

        // Override the config when loading from docker locally for debugging
        if ($_ENV['IS_DOCKER']) {
            $this->url_panel = 'https://127.0.0.1.nip.io/';
            $this->url_project = 'http://web/projects/'.Configuration::get('PLUGLIN_PROJECT');
        }
    }

    public function install()
    {
        include dirname(__FILE__).'/sql/install.php';

        $this->setContentTypes();

        Configuration::updateValue('PLUGLIN_TOKEN_ACCESS', md5(date('Y-m-d H:i:s').rand()));

        Configuration::updateValue('PLUGLIN_TOKEN', '');
        Configuration::updateValue('PLUGLIN_PROJECT', 0);
        Configuration::updateValue('PLUGLIN_LAST_SYNC', '');
        Configuration::updateValue('PLUGLIN_IS_SYNCING', 0);
        Configuration::updateValue('PLUGLIN_SYNC_ERRORS', serialize([]));
        Configuration::updateValue('PLUGLIN_TOTAL_WORDS', '');
        Configuration::updateValue('PLUGLIN_STATISTICS', serialize([]));
        Configuration::updateValue('PLUGLIN_SELECT_LANGUAGES', serialize([]));
        Configuration::updateValue('PLUGLIN_AUTOMATIC_SYNC', 0);
        Configuration::updateValue('PLUGLIN_FREQUENCY_SYNC', 24);

        $folders = [];
        foreach ($this->content_types as $key => $value) {
            $folders[$key] = '';
            unset($value);
        }
        Configuration::updateValue('PLUGLIN_ID_FOLDERS', serialize($folders));

        return parent::install()
            && $this->registerHook('actionObjectUpdateAfter')
            && $this->registerHook('actionObjectAddAfter');
    }

    public function uninstall()
    {
        include dirname(__FILE__).'/sql/uninstall.php';

        PluglinTools::closeProject();

        Configuration::deleteByName('PLUGLIN_TOKEN_ACCESS');
        Configuration::deleteByName('PLUGLIN_TOKEN');
        Configuration::deleteByName('PLUGLIN_PROJECT');
        Configuration::deleteByName('PLUGLIN_LAST_SYNC');
        Configuration::deleteByName('PLUGLIN_TOTAL_WORDS');
        Configuration::deleteByName('PLUGLIN_STATISTICS');
        Configuration::deleteByName('PLUGLIN_SELECT_LANGUAGES');
        Configuration::deleteByName('PLUGLIN_ORGANIZATION');
        Configuration::deleteByName('PLUGLIN_AUTOMATIC_SYNC');
        Configuration::deleteByName('PLUGLIN_FREQUENCY_SYNC');
        Configuration::deleteByName('PLUGLIN_IS_SYNCING');
        Configuration::deleteByName('PLUGLIN_SYNC_ERRORS');

        return parent::uninstall();
    }

    public function setContentTypes()
    {
        $this->content_types = [
            'category' => $this->l('Categories'),
            'product' => $this->l('Products'),
            'attribute_group' => $this->l('Attribute Groups'),
            'attribute' => $this->l('Attributes'),
            'feature' => $this->l('Features'),
            'feature_value' => $this->l('Feature Values'),
            'manufacturer' => $this->l('Manufacturers'),
            'supplier' => $this->l('Suppliers'),
            'cms' => $this->l('CMS Pages'),
            'theme' => $this->l('Theme'),
            'email' => $this->l('Emails'),
            'database' => $this->l('Database'),
        ];
    }

    // load dependencies in the configuration of the module.
    public function loadAsset()
    {
        // Load CSS
        $this->context->controller->addCSS([$this->css_path.'back.css'], 'all');

        // Load JS
        $this->context->controller->addJS([$this->js_path.'back.js']);
    }

    // Load the configuration form.
    public function getContent()
    {
        Configuration::updateValue('PLUGLIN_BULK_ITEMS', 100);
        $this->loadAsset();
        $apiToken = Configuration::get('PLUGLIN_TOKEN');
        $apiToken = empty($apiToken) ? '' : $apiToken;

        $this->pluglinClient = new \Pluglin\Prestashop\Services\PluglinClient($apiToken);

        $this->context->smarty->assign([
            'token_blarlo' => $apiToken,
            'url_panel' => $this->url_panel,
        ]);

        if (Tools::getValue('ajax', 0)) {
            echo $this->processAjax();
        }

        // If values have been submitted in the form, process.
        if (((bool) Tools::isSubmit('sendToken')) == true) {
            $this->postProcessToken();
        }

        if (((bool) Tools::isSubmit('sendLanguages')) == true) {
            $this->postProcessLanguages();
        }

        if (((bool) Tools::isSubmit('configureLanguages')) == true) {
            $this->postProcessLanguages();
        }

        if (((bool) Tools::isSubmit('configureSync')) == true) {
            $this->postProcessSync();
        }

        $errors = $this->errorConfigure();
        if (count($errors)) {
            $this->context->smarty->assign([
                'errors' => $errors,
            ]);
        }

        Media::addJsDef(['url_module' => $this->context->link->getAdminLink('AdminModules', true).'&configure=pluglin&module_name=pluglin']);
        Media::addJsDef(['url_cron_sync' => $this->context->link->getModuleLink('pluglin', 'cron').'?token=pluglin']);
        Media::addJsDef(['token_pluglin' => Configuration::get('PLUGLIN_TOKEN_ACCESS')]);
        Media::addJsDef(['pluglin_is_syncing' => (bool) Configuration::get('PLUGLIN_IS_SYNCING')]);
        Media::addJsDef(['_success_message' => $this->l('Proceso completado, recargando')]);
        Media::addJsDef(['_error_message' => $this->l('Error detectado: ')]);

        $this->context->smarty->assign([
            'url_cron_sync' => $this->context->link->getModuleLink('pluglin', 'cron').'?token=pluglin',
            'url_module' => $this->context->link->getAdminLink('AdminModules', true).'&configure=pluglin&module_name=pluglin',
            'token_pluglin' => Configuration::get('PLUGLIN_TOKEN_ACCESS'),
            'img_path' => $this->_path.'views/img/',
        ]);

        $this->context->smarty->assign('module_dir', $this->_path);

        /**** PLANES ****/
        if (Tools::getValue('plans')) {
            $this->loadParamsSettingsSmarty('plans');

            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/step4_plans.tpl');
        }

        /**** ESTADISTICAS DE CONTENIDOS ****/
        if (Tools::getValue('contents')) {
            $this->loadParamsSettingsSmarty('contents');

            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/step3_contents.tpl');
        }

        /**** GESTION DE LENGUAJES ****/
        if (Tools::getValue('languages')) {
            $this->loadParamsSettingsSmarty('languages');

            $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/step2_languages.tpl');

            $this->context->controller->addJqueryUI('ui.datepicker');
            $this->context->controller->addJqueryPlugin(['jscroll', 'typewatch']);

            return $output;
        }

        if (count($errors) && Tools::getValue('setToken')) {
            $this->loadParamsSettingsSmarty('languages');

            $id_default_language = (int) Configuration::get('PS_LANG_DEFAULT');
            $default_language = new Language($id_default_language, $id_default_language);
            $languages = LanguageCore::getLanguages(false);
            $selected_id_languages = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));

            if (!$selected_id_languages) {
                $selected_id_languages = [];
            }

            $load_languages = [];
            $load_languages['unselected'] = [];
            $load_languages['selected'] = [];

            foreach ($languages as $language) {
                if ($language['id_lang'] != $id_default_language) {
                    if (in_array($language['id_lang'], $selected_id_languages)) {
                        $load_languages['selected'][] = $language;
                    } else {
                        $load_languages['unselected'][] = $language;
                    }
                }
            }

            Media::addJsDef(['baseHref' => $this->context->link->getAdminLink('AdminModules').
                '&configure=pluglin&ajaxMode=1&ajax=1&action=loadLanguages&limit=20&count=0', ]);

            $this->context->smarty->assign([
                'id_default_language' => $id_default_language,
                'default_language' => $default_language->name,
                'languages' => $load_languages,
                'select_languages' => unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES')),
            ]);

            $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/step2_languages.tpl');

            $this->context->controller->addJqueryUI('ui.datepicker');
            $this->context->controller->addJqueryPlugin(['jscroll', 'typewatch']);

            return $output;
        }

        /**** CONFIGURACION DE CONTENIDOS ****/
        if (0 == count($errors) && Tools::getValue('configuration')) {
            $this->loadParamsSettingsSmarty('configuration');

            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configuration.tpl');
        }

        /**** CONFIGURACION DE SOPORTE ****/
        if (0 == count($errors) && Tools::getValue('support')) {
            $this->postProcessSupport();

            $this->loadParamsSettingsSmarty('support');

            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/support.tpl');
        }

        /**** CONFIGURACION DE TRADUCCIONES ****/
        if (0 == count($errors)) {
            $this->loadParamsSettingsSmarty('translation');

            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/translation.tpl');
        }

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/step1_welcome.tpl');
    }

    public function postProcessSupport()
    {
        $message = Tools::getValue('message');

        if (empty($message)) {
            $this->context->smarty->assign([
                'messages_pluglin' => [],
                'alert_message' => [],
            ]);

            return;
        }

        $ticketCreated = $this->pluglinClient->createSupportTicket($message);

        if ($ticketCreated) {
            $this->context->smarty->assign([
                'messages_pluglin' => [
                    'type' => 'success',
                    'content' => $this->l('Your message has been successfully sent'),
                ],
                'alert_message' => false,
            ]);
        } else {
            $this->context->smarty->assign([
                'messages_pluglin' => [],
                'alert_message' => [
                    'title' => $this->l('We could not send the message'),
                    'message' => $this->l('Please try again or write to us directly at ').
                        $this->context->smarty->fetch(_PS_MODULE_DIR_.'pluglin/views/templates/admin/helpers/mailto.tpl'),
                ],
            ]);
        }
    }

    public function loadParamsSettingsSmarty($page = '')
    {
        $menu_pluglin = [
            'translation' => [
                'name' => $this->l('Translations'),
                'selected' => ('translation' == $page),
                'url' => $this->getUrlModule('translation=1'),
            ],
            'configuration' => [
                'name' => $this->l('Settings'),
                'selected' => ('configuration' == $page),
                'url' => $this->getUrlModule('configuration=1'),
            ],
            'support' => [
                'name' => $this->l('Support'),
                'selected' => ('support' == $page),
                'url' => $this->getUrlModule('support=1'),
            ],
        ];

        if ('languages' == $page || 'configuration' == $page) {
            $id_default_language = (int) Configuration::get('PS_LANG_DEFAULT');
            $default_language = new Language($id_default_language, $id_default_language);
            $languages = LanguageCore::getLanguages(false);
            $selected_id_languages = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));

            if (!$selected_id_languages) {
                $selected_id_languages = [];
            }

            $load_languages = [];
            $load_languages['unselected'] = [];
            $load_languages['selected'] = [];

            foreach ($languages as $language) {
                if ($language['id_lang'] != $id_default_language) {
                    if (isset($selected_id_languages[$language['id_lang']])) {
                        $load_languages['selected'][] = $language;
                    } else {
                        $load_languages['unselected'][] = $language;
                    }
                }
            }

            Media::addJsDef(['baseHref' => $this->context->link->getAdminLink('AdminModules').
                '&configure=pluglin&ajaxMode=1&ajax=1&action=loadLanguages&limit=20&count=0', ]);

            $this->context->smarty->assign([
                'id_default_language' => $id_default_language,
                'default_language' => $default_language->name,
                'languages_options' => $load_languages,
                'select_languages' => unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES')),
            ]);
        }

        if ('contents' == $page) {
            if ((bool) version_compare(_PS_VERSION_, '1.7', '>=')) {
                Media::addJsDef(['content_types' => $this->content_types]);
            }
            PluglinTools::setStatistics([]);
            $this->context->smarty->assign(['contents_type' => $this->content_types]);
        }

        if ('plans' == $page || 'configuration' == $page) {
            if (PHP_MAJOR_VERSION > 5) {
                $subscription = unserialize(Configuration::get('PLUGLIN_ORGANIZATION'), ['allowed_classes' => true]);
            } else {
                $subscription = unserialize(Configuration::get('PLUGLIN_ORGANIZATION'));
            }

            $connected = $subscription && isset($subscription->plan);
            $price = $subscription->plan->price;
            $num_languages = $subscription->plan->languages;
            $available_words = $subscription->plan->words;
            $date_renew = $subscription->subscription_renewal_date;
            $used_languages = $subscription->used_languages;
            $used_words = $subscription->used_words;

            $this->context->smarty->assign([
                'connected' => $connected,
                'price' => $price,
                'num_languages' => $num_languages,
                'used_languages' => $used_languages,
                'available_words' => $available_words,
                'used_words' => $used_words,
                'date_renew' => $date_renew,
            ]);

            $plans = PluglinTools::getPlans();
            $languages = Language::getLanguages(false);
            $langs_selected = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));
            $lang_default = Configuration::get('PS_LANG_DEFAULT');

            $words = (int) Configuration::get('PLUGLIN_TOTAL_WORDS');

            $total_words = $words * count($langs_selected);

            if ($connected) {
                $total_words += $used_words;
            }

            $plan_selected = 0;
            foreach ($plans as $plan) {
                if ($plan['max_words'] > $total_words) {
                    $plan_selected = $plan['id_pluglin_plan'];
                    break;
                }
            }

            $this->context->smarty->assign([
                'words' => $words,
                'used_words' => $used_words,
                'total_words' => $total_words,
                'lang_default' => $lang_default,
                'languages' => $languages,
                'langs_selected' => $langs_selected,
                'plans' => $plans,
                'plan_selected' => $plan_selected,
            ]);
        }

        if ('translation' == $page) {
            $date_last_sync = Configuration::get('PLUGLIN_LAST_SYNC');
            if ('' == $date_last_sync) {
                $date_last_sync = $this->l('Not synced yet');
            }

            $languages = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));
            $total_words = (int) Configuration::get('PLUGLIN_TOTAL_WORDS');

            $this->context->smarty->assign([
                'date_last_sync' => $date_last_sync,
                'languages' => $languages,
                'total_words' => $total_words,
            ]);
        }

        if ('configuration' == $page) {
            $frequency_sync = (int) Configuration::get('PLUGLIN_FREQUENCY_SYNC');
            $automatic_sync = (int) Configuration::get('PLUGLIN_AUTOMATIC_SYNC');

            $this->context->smarty->assign([
                'frequency_sync' => $frequency_sync,
                'automatic_sync' => $automatic_sync,
            ]);
        }

        if ('support' == $page) {
            $this->context->smarty->assign([
                'version_module' => $this->version,
            ]);
        }

        $this->context->smarty->assign([
            'menu_pluglin' => $menu_pluglin,
            'blarlo_token' => Configuration::get('PLUGLIN_TOKEN'),
            'project' => Configuration::get('PLUGLIN_PROJECT'),
            'url_project' => $this->url_project,
        ]);
    }

    public function isConfigured()
    {
        return 0 == count($this->errorConfigure());
    }

    public function errorConfigure()
    {
        $error = [];
        if ('' == Configuration::get('PLUGLIN_TOKEN')) {
            $error[] = $this->l('Field').' '.$this->l('TOKEN').' '.$this->l('is not defined');
        }

        $langs = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));
        if (!is_array($langs) || 0 == count($langs)) {
            $error[] = $this->l('You must select at least one destination language');
        }

        $stats = unserialize(Configuration::get('PLUGLIN_STATISTICS'));

        if (!is_array($stats) || 0 == count($stats)) {
            $error[] = $this->l('You must generate the stats');
        }

        return $error;
    }

    public function hookActionObjectAddAfter($params)
    {
        $this->hookActionObjectUpdateAfter($params);
    }

    public function hookActionObjectUpdateAfter($params)
    {
        $object = $params['object'];
        switch (get_class($object)) {
            case 'Category':
                PluglinTools::addContentToSend('category', $object->id);
                break;
            case 'Product':
                PluglinTools::addContentToSend('product', $object->id);
                break;
            case 'Supplier':
                PluglinTools::addContentToSend('supplier', $object->id);
                break;
            case 'Manufacturer':
                PluglinTools::addContentToSend('manufacturer', $object->id);
                break;
            case 'Feature':
                PluglinTools::addContentToSend('feature', $object->id);
                break;
            case 'FeatureValue':
                PluglinTools::addContentToSend('feature_value', $object->id);
                break;
            case 'Attribute':
                PluglinTools::addContentToSend('attribute', $object->id);
                break;
            case 'AttributeGroup':
                PluglinTools::addContentToSend('attribute_group', $object->id);
                break;
            case 'CMS':
                PluglinTools::addContentToSend('cms', $object->id);
                break;
            case 'Theme':
                PluglinTools::addContentToSend('theme', 0);
                break;
        }
    }

    /**** PROCESS AJAX ***/
    private function processAjax()
    {
        switch (Tools::getValue('action', 0)) {
            case 'getContentStatsNumber':
                $this->displayAjaxGetContentStatsNumber();
                break;
            case 'getContentWordsNumber':
                $this->displayAjaxGetContentWordsNumber();
                break;
        }
        die();
    }

    private function postProcessToken()
    {
        $token = Tools::getValue('token_blarlo');
        $error = [];

        $api = new PluglinClient();
        $data = [];
        $data['token'] = $token;

        $response = $api->__call('organization', $data);

        if (!isset($response['code']) || 200 != $response['code']) {
            $error['title'] = $this->l('Wrong Token supplied');
            $error['message'] = $this->l('We could not connect with Pluglin');

            $this->context->smarty->assign(['alert_message' => $error]);

            Configuration::updateValue('PLUGLIN_TOKEN', '');
            Configuration::updateValue('PLUGLIN_ORGANIZATION', serialize([]));

            return $error;
        }

        Configuration::updateValue('PLUGLIN_TOKEN', $token);
        Configuration::updateValue('PLUGLIN_ORGANIZATION', serialize(json_decode($response['message'])));
        Configuration::updateValue('PLUGLIN_PROJECT', 0);
        Configuration::updateValue('PLUGLIN_LAST_SYNC', '');
        Configuration::updateValue('PLUGLIN_TOTAL_WORDS', '');

        PluglinTools::cleanContents();

        $_GET['languages'] = 1;

        return null;
    }

    private function postProcessLanguages()
    {
        $selectedLanguages = Tools::getValue('language_select');

        if (!is_array($selectedLanguages) || count($selectedLanguages) < 1) {
            $this->context->smarty->assign([
                'alert_message' => [
                    'title' => $this->l('We could not create the project in Pluglin'),
                    'message' => $this->l('At least one language is required'),
                ],
            ]);

            $_GET['setToken'] = 1;
            Configuration::updateValue('PLUGLIN_SELECT_LANGUAGES', serialize([]));

            return;
        }

        $languages = [];
        foreach ($selectedLanguages as $id_lang) {
            $lang = new Language((int) $id_lang);

            $languages[$id_lang] = [
                'id_lang' => $id_lang,
                'name' => $lang->name,
                'code' => $lang->iso_code,
                'translated' => 0,
                'revised' => 0,
            ];
        }

        Configuration::updateValue('PLUGLIN_SELECT_LANGUAGES', serialize($languages));

        // Creamos o actualizamos el projecto en Pluglin
        $createProjectErrors = PluglinTools::openProject();

        if (empty($createProjectErrors)) {
            $_GET['contents'] = 1;

            return;
        }

        $brHtml = $this->context->smarty->fetch(_PS_MODULE_DIR_.'pluglin/views/templates/admin/helpers/br.tpl');
        $mailToHtml = $this->context->smarty->fetch(_PS_MODULE_DIR_.'pluglin/views/templates/admin/helpers/mailto.tpl');

        $this->context->smarty->assign([
            'alert_message' => [
                'title' => $this->l('We could not create the project in Pluglin'),
                'message' => sprintf(
                    '"%s"%s %s %s',
                    $createProjectErrors['message'],
                    $brHtml,
                    $this->l('Please try again or write to us directly at '),
                    $mailToHtml
                ),
            ],
        ]);

        $_GET['setToken'] = 1;
        Configuration::updateValue('PLUGLIN_SELECT_LANGUAGES', serialize([]));
    }

    private function postProcessSync()
    {
        $automatic_sync = (int) Tools::getValue('automatic_sync');
        $frequency_sync = (int) Tools::getValue('frequency_sync');

        Configuration::updateValue('PLUGLIN_AUTOMATIC_SYNC', $automatic_sync);
        Configuration::updateValue('PLUGLIN_FREQUENCY_SYNC', $frequency_sync);

        PluglinTools::updateProject();
    }

    private function loadParamsSmarty()
    {
        $this->context->smarty->assign(['blarlo_token' => Configuration::get('PLUGLIN_TOKEN')]);
    }

    public function displayAjaxGetContentStatsNumber()
    {
        $type = Tools::getValue('type_content');
        $statistics = PluglinTools::getStatistics();

        if ('database' === $type) {
            $dbExtractor = new Pluglin\Prestashop\Extractors\Database(Configuration::get('PS_LANG_DEFAULT'));
            $dbExtractor->extractData();
            $count = $dbExtractor->getWordCount();
        } else {
            $count = PluglinTools::countType(Tools::getValue('type_content'));
        }

        $statistics[$type]['items'] = $count;
        PluglinTools::setStatistics($statistics);

        die(json_encode([
            'count' => $count,
        ]));
    }

    public function displayAjaxGetContentWordsNumber()
    {
        $type = Tools::getValue('type_content');
        $nb = (int) Configuration::get('PLUGLIN_BULK_ITEMS');
        $statistics = PluglinTools::getStatistics();

        $items = $statistics[$type]['items'];
        $last_item = $statistics[$type]['last_item'];

        if ('database' === $type) {
            $dbExtractor = new Pluglin\Prestashop\Extractors\Database(Configuration::get('PS_LANG_DEFAULT'));
            $dbExtractor->extractData();
            $count = $dbExtractor->getWordCount();
            $last_item = $items; // we don't iterate on this one for the time being.
        } else {
            $count = PluglinTools::countWordsType($type, $last_item);
        }

        $statistics[$type]['words'] += $count;

        $last_item += $nb;

        if ($last_item > $items) {
            $statistics[$type]['last_item'] = $items;
            $last_item = $items;
            $next = false;
        } else {
            $statistics[$type]['last_item'] = $last_item;
            $next = true;
        }

        PluglinTools::setStatistics($statistics);

        die(json_encode([
            'count' => $count,
            'last_item' => $last_item,
            'items' => $items,
            'nb' => $nb,
            'next' => $next,
        ]));
    }

    public function getContentTypes()
    {
        return $this->content_types;
    }

    public function getUrlModule($params = '')
    {
        $url = $this->context->link->getAdminLink('AdminModules', true).'&configure=pluglin&module_name=pluglin';

        if ('' != $params) {
            $url .= '&'.$params;
        }

        return $url;
    }
}
