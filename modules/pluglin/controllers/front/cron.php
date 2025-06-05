<?php

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

$_ENV['IS_DOCKER'] = false;

require_once _PS_MODULE_DIR_.'pluglin/vendor/autoload.php';

class PluglinCronModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    private $pluglinClient;
    private $api_key;
    private $defaultLanguage;
    private $projectName;
    private $result = [];

    public function __construct()
    {
        $this->api_key = Configuration::get('PLUGLIN_TOKEN');
        $this->pluglinClient = new PluglinClient();
        $this->defaultLanguage = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $this->projectName = Configuration::get('PS_SHOP_NAME');

        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        // AUTH
        $access_token = Tools::getValue('token');
        if ($access_token != Configuration::get('PLUGLIN_TOKEN_ACCESS')) {
            return jsonResponse([
                'error' => 'Incorrect access token',
            ], 401);
        }

        $method = Tools::getValue('method');

        if ('progress' === $method) {
            return jsonResponse($this->checkProgress());
        }

        if ('callback' === $method) {
            $isImported = $this->import([
                'token' => $this->api_key,
                'project' => (int) Configuration::get('PLUGLIN_PROJECT'),
                'downloadId' => (int) Configuration::get('PLUGLIN_DATAPACK_ID'),
            ]);

            if (!$isImported) {
                Configuration::updateValue('PLUGLIN_SYNC_ERRORS', serialize($this->result));
                Configuration::updateValue('PLUGLIN_IS_SYNCING', false);

                return jsonResponse($this->result);
            }

            Configuration::updateValue('PLUGLIN_LAST_SYNC', date('d/m/Y H:i:s'));

            return jsonResponse(['DONE']);
        }

        // SE DETERMINAN LOS IDIOMAS DE DESTINO
        $languages = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));
        $languages_code = [];
        foreach ($languages as $lang) {
            $languages_code[] = $lang['code'];
        }

        $data = [];
        $data['token'] = $this->api_key;
        $this->result = [];
        $projectID = (int) Configuration::get('PLUGLIN_PROJECT');

        switch ($method) {
            case 'organization':
            case 'langs':
            case 'validate':
                $this->pluglinClient->__call($method, $data);
                break;
            case 'pairs':
                $data['source'] = Tools::getValue('source');
                $this->pluglinClient->__call($method, $data);
                break;
            case 'delProject':
            case 'getProject':
                $data['id'] = $projectID;
                $this->pluglinClient->__call($method, $data);
                break;
            case 'newProject':
                $data['name'] = $this->projectName;
                $data['source'] = $this->defaultLanguage->iso_code;
                $data['destinations'] = $languages_code;
                $this->pluglinClient->__call($method, $data);
                break;
            case 'updProject':
                $data['id'] = $projectID;
                $data['name'] = $this->projectName;
                $data['source'] = $this->defaultLanguage->iso_code;
                $data['destinations'] = $languages_code;
                $this->pluglinClient->__call($method, $data);
                break;
            case 'contents':
                $data['id'] = Tools::getValue('source');
                $this->pluglinClient->__call($method, $data);
                break;
            case 'upload':
                if (!$projectID) {
                    $this->result['error'][] = 'No se ha definido proyecto';
                }

                $this->upload($projectID, $data);
                break;
            case 'getDownload':
                if (!$projectID) {
                    $this->result['error'][] = 'No se ha definido proyecto';
                }

                $this->download($projectID, $data);
                break;
            case 'sync':
                if (!$projectID) {
                    $this->result['error'][] = 'No se ha definido proyecto';
                    break;
                }

                Configuration::updateValue('PLUGLIN_IS_SYNCING', true);

                $this->upload($projectID, $data);
                $this->download($projectID, $data);

                break;
            default:
                $this->result['error'][] = "$method method does not exist";
        }

        if ($this->pluglinClient->last_error) {
            $this->result['error'][] = $this->pluglinClient->last_error;
        }

        $this->result['request'] = $this->pluglinClient->last_request;
        $this->result['response'] = $this->pluglinClient->last_response;
        $this->result['raw_response'] = $this->pluglinClient->last_response_raw;

        if (array_key_exists('error', $this->result) && count($this->result['error']) > 0) {
            $this->pluglinClient->last_error = serialize($this->result['error']);
        }

        if ('sync' === $method) {
            if ($this->pluglinClient->hasErrors()) {
                if (array_key_exists('error', $this->result)) {
                    $this->result['error'] = [
                        $this->result['error'],
                        $this->pluglinClient->last_error,
                    ];
                } else {
                    $this->result['error'] = $this->pluglinClient->last_error;
                }

                Configuration::updateValue('PLUGLIN_IS_SYNCING', false);
                Configuration::updateValue('PLUGLIN_SYNC_ERRORS', serialize($this->result));
            }
        }

        return jsonResponse($this->result);
    }

    private function upload($id_project, $data)
    {
        $this->pluglinClient->__call('log', [
            'token' => Configuration::get('PLUGLIN_TOKEN'),
            'level' => 'info',
            'message' => 'Prestashop => Pluglin upload STARTED',
            'data' => [
                'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
            ],
        ]);

        $exporter = new \Pluglin\Prestashop\Services\Exporter();
        $exporter->saveToFile();

        if ('' == $exporter->getFilename()) {
            $this->pluglinClient->__call('log', [
                'token' => Configuration::get('PLUGLIN_TOKEN'),
                'level' => 'info',
                'message' => 'Prestashop => Pluglin upload OK',
                'data' => [
                    'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
                    'info' => 'No data to send',
                ],
            ]);

            $this->result['ok'] = true;

            return;
        }

        $url = $this->context->getContext()->shop->getBaseURL(true, true).'modules/pluglin/files/upload/'.$exporter->getFilename();

        $data['project'] = $id_project;
        $data['url'] = $url;

        if ($_ENV['IS_DOCKER']) {
            // substitute your local name, per your docker prestashop instance hostname
            $data['url'] = str_replace('localhost:8091', 'presta17', $url);
            $data['url'] = str_replace('localhost:8090', 'presta16', $url);
        }

        $this->pluglinClient->__call('upload', $data);

        $uploadResult = true;
        if ($this->pluglinClient->hasErrors()) {
            // TRATAMOS EL ERROR
            PluglinTools::cancelSendItems();
            $uploadResult = false;

            $this->pluglinClient->__call('log', [
                'token' => Configuration::get('PLUGLIN_TOKEN'),
                'level' => 'info',
                'message' => 'Prestashop => Pluglin upload KO',
                'data' => [
                    'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
                    'error' => $this->pluglinClient->last_error,
                ],
            ]);
        } else {
            $id_data = $this->pluglinClient->last_response['id'];
            $status_data = $this->pluglinClient->last_response['status'];
            // LO DAMOS POR ENVIADO
            PluglinTools::readSentItems($id_data, $status_data);
            $uploadResult = true;
        }

        $this->pluglinClient->__call('log', [
            'token' => Configuration::get('PLUGLIN_TOKEN'),
            'level' => 'info',
            'message' => 'Prestashop => Pluglin upload OK',
            'data' => [
                'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
            ],
        ]);

        $this->result['data'] = $this->pluglinClient->last_response;
        $this->result['ok'] = $uploadResult;
    }

    private function download($projectId, $data)
    {
        $this->pluglinClient->__call('log', [
            'token' => Configuration::get('PLUGLIN_TOKEN'),
            'level' => 'info',
            'message' => 'Prestashop <= Pluglin download STARTED',
            'data' => [
                'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
            ],
        ]);

        $accesToken = Configuration::get('PLUGLIN_TOKEN_ACCESS');
        $data['project'] = $projectId;
        $data['callback'] = $this->context->link->getModuleLink('pluglin', 'cron')."?token=$accesToken&ajax=1&method=callback";
        $data['from_date'] = empty(Configuration::get('PLUGLIN_LAST_SYNC')) ? null : Configuration::get('PLUGLIN_LAST_SYNC');

        if ($_ENV['IS_DOCKER']) {
            $data['callback'] = str_replace('localhost:8091', 'presta17', $this->context->link->getModuleLink('pluglin', 'cron')."?token=$accesToken&ajax=1&method=callback");
            $data['callback'] = str_replace('localhost:8090', 'presta16', $this->context->link->getModuleLink('pluglin', 'cron')."?token=$accesToken&ajax=1&method=callback");
        }

        $response = $this->pluglinClient->__call('download', $data);

        if (!isset($response)) {
            $this->result['data'] = $data;
            $this->result['error'] = 'download of data failed';

            return;
        }

        if (200 != $response['code']) {
            $this->result['code'] = $response['code'];
            $this->result['response'] = $response;
            $this->result['error'] = [
                'message' => 'response returned different than 200',
                '$response' => $response,
            ];

            return;
        }

        $message = json_decode($response['message'], 1);
        Configuration::updateValue('PLUGLIN_DATAPACK_ID', (int) $message['id']);

        $this->pluglinClient->__call('log', [
            'token' => Configuration::get('PLUGLIN_TOKEN'),
            'level' => 'info',
            'message' => 'Prestashop <= Pluglin download OK',
            'data' => [
                'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
            ],
        ]);
    }

    private function checkProgress(): array
    {
        $isSyncing = (bool) Configuration::get('PLUGLIN_IS_SYNCING');
        if (true === $isSyncing) {
            return [
                'syncing' => true,
            ];
        }

        $finishedData = [];
        $syncErrors = unserialize(Configuration::get('PLUGLIN_SYNC_ERRORS'));
        if (!empty($syncErrors)) {
            $finishedData = [
                'has_error' => true,
                'message' => implode("\n", $syncErrors),
            ];
        }

        $finishedData['syncing'] = false;

        // reset errors in DB
        Configuration::updateValue('PLUGLIN_SYNC_ERRORS', null);

        return $finishedData;
    }

    private function import($data): bool
    {
        $this->pluglinClient->__call('log', [
            'token' => Configuration::get('PLUGLIN_TOKEN'),
            'level' => 'info',
            'message' => 'Prestashop <= Pluglin import STARTED',
            'data' => [
                'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
            ],
        ]);

        $response = $this->pluglinClient->__call('getDownload', $data);

        if (isset($response['code']) && 200 == $response['code']) {
            $message = json_decode($response['message'], 1);

            if ('running' != $message['status']) {
                $url = $message['download_url'];
            }
        }

        if (empty($url)) {
            $this->pluglinClient->__call('log', [
                'token' => Configuration::get('PLUGLIN_TOKEN'),
                'level' => 'info',
                'message' => 'Prestashop <= Pluglin import IN PROGRESS',
                'data' => [
                    'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
                ],
            ]);

            return false;
        }

        $importer = new ImportContents();
        $importer->importData($data['downloadId'], $url);
        $importer->checkOldFiles();

        Configuration::updateValue('PLUGLIN_IS_SYNCING', false);

        $this->pluglinClient->__call('log', [
            'token' => Configuration::get('PLUGLIN_TOKEN'),
            'level' => 'info',
            'message' => 'Prestashop <= Pluglin import OK',
            'data' => [
                'clientToken' => Configuration::get('PLUGLIN_TOKEN'),
                'url' => $url,
            ],
        ]);

        return true;
    }
}
