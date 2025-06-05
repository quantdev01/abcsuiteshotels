<?php

/**
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder).
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf
 *            https://www.lineagrafica.es/licenses/license_es.pdf
 *            https://www.lineagrafica.es/licenses/license_fr.pdf
 */
class PluglinTools
{
    public const CHARLIST = 'áéíóúÁÉÍÓÚ';

    public static function countType($type)
    {
        switch ($type) {
            case 'category':
                return self::countCategories();
            case 'product':
                return self::countProducts();
            case 'attribute_group':
                return self::countAttributeGroups();
            case 'attribute':
                return self::countAttributes();
            case 'feature':
                return self::countFeatures();
            case 'feature_value':
                return self::countFeatureValues();
            case 'manufacturer':
                return self::countManufacturers();
            case 'supplier':
                return self::countSuppliers();
            case 'cms':
                return self::countCms();
            case 'theme':
                return self::countTheme();
            case 'database':
                // should have been done before this
        }

        return 0;
    }

    public static function countTheme16()
    {
        //Locate te origin language
        $translationLanguageID = (int) Configuration::get('PS_LANG_DEFAULT');
        $languageObj = new LanguageCore($translationLanguageID);
        $selectedThemeDir = _PS_ROOT_DIR_.'/themes/'.Context::getContext()->shop->theme_directory.'/';

        //Locate the template path
        $themeTranslationsPath = $selectedThemeDir.'lang/';
        $globalTranslationsPath = _PS_TRANSLATIONS_DIR_;

        //Locate the files
        $themeTranslations = $themeTranslationsPath.$languageObj->iso_code.'.php';
        $globalTranslations = $globalTranslationsPath.$languageObj->iso_code.'/fields.php';

        global $_LANG;
        //Read the file translations
        if (file_exists($themeTranslations)) {
            require_once $themeTranslations;
        }
        if (file_exists($globalTranslations)) {
            require_once $globalTranslations;
        }

        $words_count = 0;
        foreach ($_LANG as $phrase) {
            $words_count += str_word_count($phrase);
        }

        return $words_count;
    }

    public static function countTheme()
    {
        //Is a 1.6.x?
        if ((bool) version_compare(_PS_VERSION_, '1.7', '<')) {
            return self::countTheme16();
        }

        // Execute for 1.7
        $translationLanguageID = (int) Configuration::get('PS_LANG_DEFAULT');
        $langObj = new LanguageCore($translationLanguageID);
        $translator = Context::getContext()->getTranslatorFromLocale($langObj->locale);

        $translateTheme = $translator->getCatalogue($langObj->locale)->all();

        $wordFakeId = 0;
        foreach ($translateTheme as $domain => $translateDomain) {
            foreach ($translateDomain as $originalTranslate => $frase) {
                //Increment the word id
                unset($originalTranslate);
                unset($domain);
                unset($frase);
                ++$wordFakeId;
            }
        }

        return $wordFakeId;
    }

    public static function countCategories($all = true)
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'category ';
        if (!$all) {
            $sql = ' WHERE active=1';
        }

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countProducts($all = true)
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'product ';
        if (!$all) {
            $sql = ' WHERE active=1';
        }

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countAttributeGroups()
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'attribute_group ';

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countAttributes()
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'attribute ';

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countFeatures()
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'feature ';

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countFeatureValues()
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'feature_value ';

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countManufacturers()
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'manufacturer ';

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countSuppliers()
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'supplier ';

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countCms()
    {
        $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'cms ';

        return (int) DB::getInstance()->getValue($sql);
    }

    public static function countWordsType($type, $lastItem)
    {
        switch ($type) {
            case 'category':
                return self::countWordsCategories($lastItem);
            case 'product':
                return self::countWordsProducts($lastItem);
            case 'attribute_group':
                return self::countWordsAttributeGroups($lastItem);
            case 'attribute':
                return self::countWordsAttributes($lastItem);
            case 'feature':
                return self::countWordsFeatures($lastItem);
            case 'feature_value':
                return self::countWordsFeatureValues($lastItem);
            case 'manufacturer':
                return self::countWordsManufacturers($lastItem);
            case 'supplier':
                return self::countWordsSuppliers($lastItem);
            case 'cms':
                return self::countWordsCms($lastItem);
            case 'theme':
                return self::countWordsTheme($lastItem);
            case 'email':
                return self::countWordsEmail();
            case 'database':
                // done before this step.
        }

        return 0;
    }

    public static function countWordsCategories($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');
        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'category_lang WHERE id_lang= '.$id_lang;
        $sql .= ' GROUP BY id_category LIMIT '.(int) $from.','.$num_item;

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['name']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['description']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_title']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_keywords']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_description']), 0, self::CHARLIST);

            $num_words_total += $num_words;

            self::addContentToSend('category', (int) $row['id_category']);
        }

        return $num_words_total;
    }

    public static function countWordsProducts($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'product_lang WHERE id_lang='.$id_lang;

        $sql .= ' GROUP BY id_product LIMIT '.(int) $from.','.$num_item;

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['name']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['description']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['description_short']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_keywords']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_description']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_title']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['available_now']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['available_later']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['delivery_in_stock']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['delivery_out_stock']), 0, self::CHARLIST);

            $num_words_total += $num_words;

            self::addContentToSend('product', (int) $row['id_product']);
        }

        return $num_words_total;
    }

    public static function countWordsAttributeGroups($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'attribute_group_lang WHERE id_lang='.$id_lang;

        $sql .= ' GROUP BY id_attribute_group LIMIT '.(int) $from.','.$num_item;

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['name']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['public_name']), 0, self::CHARLIST);
            $num_words_total += $num_words;

            self::addContentToSend('attribute_group', (int) $row['id_attribute_group']);
        }

        return $num_words_total;
    }

    public static function countWordsAttributes($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'attribute_lang WHERE id_lang='.$id_lang;

        $sql .= ' GROUP BY id_attribute LIMIT '.(int) $from.','.$num_item;

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['name']), 0, self::CHARLIST);
            $num_words_total += $num_words;

            self::addContentToSend('attribute', (int) $row['id_attribute']);
        }

        return $num_words_total;
    }

    public static function countWordsFeatures($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'feature_lang WHERE id_lang='.$id_lang;
        $sql .= ' GROUP BY id_feature LIMIT '.(int) $from.','.($num_item);

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['name']), 0, self::CHARLIST);
            $num_words_total += $num_words;

            self::addContentToSend('feature', (int) $row['id_feature']);
        }

        return $num_words_total;
    }

    public static function countWordsFeatureValues($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'feature_value_lang WHERE id_lang='.$id_lang;

        $sql .= ' GROUP BY id_feature_value LIMIT '.(int) $from.','.($num_item);

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['value']), 0, self::CHARLIST);
            $num_words_total += $num_words;

            self::addContentToSend('feature_value', (int) $row['id_feature_value']);
        }

        return $num_words_total;
    }

    public static function countWordsManufacturers($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'manufacturer_lang WHERE id_lang='.$id_lang;

        $sql .= ' GROUP BY id_manufacturer LIMIT '.(int) $from.','.$num_item;

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['description']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['short_description']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_title']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_keywords']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_description']), 0, self::CHARLIST);
            $num_words_total += $num_words;

            self::addContentToSend('manufacturer', (int) $row['id_manufacturer']);
        }

        return $num_words_total;
    }

    public static function countWordsSuppliers($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'supplier_lang WHERE id_lang='.$id_lang;

        $sql .= ' GROUP BY id_supplier LIMIT '.(int) $from.','.$num_item;

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['description']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_title']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_keywords']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_description']), 0, self::CHARLIST);
            $num_words_total += $num_words;

            self::addContentToSend('supplier', (int) $row['id_supplier']);
        }

        return $num_words_total;
    }

    public static function countWordsCms($from)
    {
        $num_item = (int) Configuration::get('PLUGLIN_BULK_ITEMS');

        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = 'SELECT * FROM '._DB_PREFIX_.'cms_lang WHERE id_lang='.$id_lang;

        $sql .= ' GROUP BY id_cms LIMIT '.(int) $from.','.$num_item;

        $result = DB::getInstance()->executeS($sql);

        $num_words_total = 0;
        foreach ($result as $row) {
            $num_words = 0;
            $num_words += str_word_count(strip_tags($row['content']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['head_seo_title']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_title']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_keywords']), 0, self::CHARLIST);
            $num_words += str_word_count(strip_tags($row['meta_description']), 0, self::CHARLIST);
            $num_words_total += $num_words;

            self::addContentToSend('cms', (int) $row['id_cms']);
        }

        return $num_words_total;
    }

    public static function countWordsTheme16($from)
    {
        //Locate te origin language
        $translationLanguageID = (int) Configuration::get('PS_LANG_DEFAULT');
        $languageObj = new LanguageCore($translationLanguageID);
        $selectedThemeDir = _PS_ROOT_DIR_.'/themes/'.Context::getContext()->shop->theme_directory.'/';

        //Locate the template path
        $themeTranslationsPath = $selectedThemeDir.'lang/';
        $globalTranslationsPath = _PS_TRANSLATIONS_DIR_;

        //Locate the files
        $themeTranslations = $themeTranslationsPath.$languageObj->iso_code.'.php';
        $globalTranslations = $globalTranslationsPath.$languageObj->iso_code.'/fields.php';

        global $_LANG;
        $words_count = 0;
        $num_items = (int) Configuration::get('PLUGLIN_BULK_ITEMS');
        $processed_items = 0;
        $wordFakeId = 0;

        //Read the file translations
        if (file_exists($themeTranslations)) {
            $_LANG = [];
            require_once $themeTranslations;

            //Process the "front" translations
            foreach ($_LANG as $key => $phrase) {
                //Pagination control, from, and return
                if ($from > $wordFakeId) {
                    ++$wordFakeId;
                    continue;
                } else {
                    if ($processed_items >= $num_items) {
                        return $words_count;
                    }
                }

                //Add the item to store
                self::addContentToSend(
                    'theme',
                    $words_count,
                    '',
                    [
                            'file' => $themeTranslations,
                            'key' => $key,
                            'basePath' => $themeTranslationsPath,
                            'type' => 'theme',
                        ]
                );

                $words_count += str_word_count($phrase);
            }
        }
        if (file_exists($globalTranslations)) {
            //Process the "fields" global, translations
            $_LANG = [];
            require_once $globalTranslations;

            foreach ($_LANG as $key => $phrase) {
                //Pagination control, from, and return
                if ($from > $wordFakeId) {
                    ++$wordFakeId;
                    continue;
                } else {
                    if ($processed_items >= $num_items) {
                        return $words_count;
                    }
                }

                //Add the item to store
                self::addContentToSend(
                    'theme',
                    $words_count,
                    '',
                    [
                        'file' => $globalTranslations,
                        'key' => $key,
                        'basePath' => $globalTranslationsPath,
                        'type' => 'field',
                    ]
                );

                $words_count += str_word_count($phrase);
            }
        }

        return $words_count;
    }

    public static function countWordsTheme($from)
    {
        //Is a 1.6.x?
        if ((bool) version_compare(_PS_VERSION_, '1.7', '<')) {
            return self::countWordsTheme16($from);
        }

        //Execute for 1.7
        $num_items = (int) Configuration::get('PLUGLIN_BULK_ITEMS');
        $processed_items = 0;

        $translationLanguageID = (int) Configuration::get('PS_LANG_DEFAULT');
        $langObj = new LanguageCore($translationLanguageID);
        $translator = Context::getContext()->getTranslatorFromLocale($langObj->locale);

        $translateTheme = $translator->getCatalogue($langObj->locale)->all();

        $wordFakeId = 0;
        $num_words_total = 0;
        foreach ($translateTheme as $domain => $translateDomain) {
            //check if domain type is theme, not backoffice or module
            if (preg_match('/^ShopTheme/', $domain)) {
                $domain_type = true;
            } else {
                $domain_type = false;
            }

            foreach ($translateDomain as $originalTranslate => $frase) {
                //Pagination control, from, and return
                if ($from > $wordFakeId) {
                    ++$wordFakeId;
                    continue;
                } else {
                    if ($processed_items >= $num_items) {
                        return $num_words_total;
                    }
                }

                //Count the words
                $num_words = 0;
                $num_words += str_word_count(strip_tags($originalTranslate), 0, self::CHARLIST);
                $num_words_total += $num_words;

                //Add the item to store
                self::addContentToSend(
                    'theme',
                    $wordFakeId,
                    '',
                    [
                        'is_theme' => $domain_type,
                        'domain' => $domain,
                        'original_ps' => base64_encode($originalTranslate), //original translation english
                        'original' => base64_encode($frase),
                    ]
                );

                //Increment the word id
                ++$wordFakeId;
                ++$processed_items;
            }
            ++$num_words_total;
        }

        return $num_words_total;
    }

    public static function countWordsEmail()
    {
        $ignore_folder = ['.', '..', '.svn', '.git', '.htaccess', 'index.php'];
        $defaultLangISO = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
        $mails = [];
        if (Tools::file_exists_cache(_PS_MAIL_DIR_.$defaultLangISO.'/')) {
            $arr_files = scandir(_PS_MAIL_DIR_.$defaultLangISO.'/', SCANDIR_SORT_NONE);
            foreach ($arr_files as $file) {
                if (!in_array($file, $ignore_folder)) {
                    if (Tools::file_exists_cache(_PS_MAIL_DIR_.$defaultLangISO.'/'.$file)) {
                        $mails[] = _PS_MAIL_DIR_.$defaultLangISO.'/'.$file;
                    }
                }
            }
        }

        $mails_theme = [];
        if (Tools::file_exists_cache(_PS_THEME_DIR_.'mails/'.$defaultLangISO.'/')) {
            $arr_files = scandir(_PS_THEME_DIR_.'mails/'.$defaultLangISO.'/', SCANDIR_SORT_NONE);
            foreach ($arr_files as $file) {
                if (!in_array($file, $ignore_folder)) {
                    if (Tools::file_exists_cache(_PS_THEME_DIR_.'mails/'.$defaultLangISO.'/'.$file)) {
                        $mails[] = _PS_THEME_DIR_.'mails/'.$defaultLangISO.'/'.$file;
                    }
                }
            }
        }

        $modules = scandir(_PS_MODULE_DIR_, SCANDIR_SORT_NONE);

        $module_mails = [];

        foreach ($modules as $module) {
            if (!in_array($module, $ignore_folder) &&
                Tools::file_exists_cache(_PS_MODULE_DIR_.$module.'/mails/'.$defaultLangISO.'/')
                ) {
                $arr_files = scandir(
                    _PS_MODULE_DIR_.$module.'/mails/'.$defaultLangISO.'/',
                    SCANDIR_SORT_NONE
                );

                foreach ($arr_files as $file) {
                    if (!in_array($file, $ignore_folder)) {
                        if (Tools::file_exists_cache(
                            _PS_MODULE_DIR_.$module.'/mails/'.$defaultLangISO.'/'.$file
                        )
                            ) {
                            $module_mails[] = _PS_MODULE_DIR_.$module.'/mails/'.$defaultLangISO.'/'.$file;
                        }
                    }
                }
            }
        }

        $mails = array_merge($mails, $mails_theme, $module_mails);

        //Write to the table
        $i = 0;
        $words = 0;
        foreach ($mails as $email) {
            $content = Tools::file_get_contents($email);
            $words += str_word_count(strip_tags($content), 0, self::CHARLIST);

            self::addContentToSend(
                'email',
                $i,
                false,
                [
                    'path' => $email,
                ]
            );
            ++$i;
        }

        return $words;
    }

    public static function getStatistics()
    {
        $pluglin = Module::getInstanceByName('pluglin');

        $stats = unserialize(Configuration::get('PLUGLIN_STATISTICS'));

        if (!is_array($stats) || 0 == count($stats)) {
            $stats = [];

            foreach ($pluglin->getContentTypes() as $key => $value) {
                $data = [];
                $data['items'] = 0;
                $data['last_item'] = 0;
                $data['words'] = 0;

                $stats[$key] = $data;
                unset($value);
            }
            self::setStatistics($stats);
        }

        return $stats;
    }

    public static function setStatistics($stats)
    {
        $total_words = 0;
        foreach ($stats as $stat) {
            $total_words += (int) $stat['words'];
        }

        Configuration::updateValue('PLUGLIN_TOTAL_WORDS', $total_words);
        Configuration::updateValue('PLUGLIN_STATISTICS', serialize($stats));
    }

    public static function getPlans()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'pluglin_plan order by price';

        return DB::getInstance()->executeS($sql);
    }

    public static function cleanContents()
    {
        $sql = 'DELETE FROM '._DB_PREFIX_.'pluglin_content';
        DB::getInstance()->execute($sql);
    }

    public static function addContentToSend($type_content, $id_content, $id_pluglin = '', $data_json = [])
    {
        $data = [];
        $data['type'] = $type_content;
        $data['id_content'] = $id_content;
        $data['read'] = 0;
        $data['send'] = 0;
        if ($id_pluglin) {
            $data['id_pluglin'] = $id_pluglin;
        }
        $data['date_add'] = date('Y-m-d H:i:s');
        $data['data_json'] = json_encode($data_json);
        $data['data_json'] = str_replace("'", "\'", $data['data_json']);

        $sql = 'SELECT id_pluglin_content FROM '._DB_PREFIX_."pluglin_content WHERE type='".$data['type']."' AND id_content= ".(int) $data['id_content'];
        $id_pluglin_content = (int) DB::getInstance()->getValue($sql);

        if ($id_pluglin_content) {
            Db::getInstance()->update('pluglin_content', $data, 'id_pluglin_content='.$id_pluglin_content);
        } else {
            Db::getInstance()->insert('pluglin_content', $data, false, false);
        }
    }

    public static function contentSent($type_content, $id_content)
    {
        $sql = 'UPDATE `'._DB_PREFIX_."pluglin_content` SET `send` = '1' WHERE `type` = '".
            $type_content."' AND `id_content` = ".$id_content;
        Db::getInstance()->execute($sql);
    }

    public static function getContentToSend($limit = 10)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'pluglin_content WHERE `send`=0 AND `read`=0 LIMIT 0,'.$limit;

        return Db::getInstance()->executeS($sql);
    }

    public static function cancelSendItems()
    {
        $sql = 'UPDATE '._DB_PREFIX_.'pluglin_content SET `send`=0 WHERE `send`=1 AND `read`=0';

        return Db::getInstance()->execute($sql);
    }

    public static function readSentItems($id_data, $status_data)
    {
        $sql = 'UPDATE '._DB_PREFIX_.'pluglin_content SET `read`=1, `package`='.(int) $id_data.", `status`='".
            $status_data."' WHERE `send`=1 AND `read`=0";

        return Db::getInstance()->execute($sql);
    }

    public static function hasContentToSend()
    {
        $sql = 'SELECT count(1) FROM '._DB_PREFIX_.'pluglin_content WHERE `send`=0';

        return (int) Db::getInstance()->getValue($sql);
    }

    public static function openProject()
    {
        if ((int) Configuration::get('PLUGLIN_PROJECT')) {
            return self::updateProject();
        }

        $api = new PluglinClient();
        list($selectedLangsIDs, $data) = self::prepareProjectData();

        $api->__call('newProject', $data);

        $response = $api->last_response;
        if (!is_array($response)) {
            return [[
                'message' => 'Not Connected',
            ]];
        }

        if (!isset($response['id']) || !(int) $response['id']) {
            return [[
              'message' => $response['message'],
            ]];
        }

        Configuration::updateValue('PLUGLIN_PROJECT', (int) $response['id']);

        self::updateLanguagesData($selectedLangsIDs, $response['destinations']);

        // Definimos los tipos de carpeta
        $folders = unserialize(Configuration::get('PLUGLIN_ID_FOLDERS'));

        foreach ($folders as $key => $folder) {
            $result = self::createFolder($key);
            $folders[$key] = (int) $result;
            unset($folder);
        }
        Configuration::updateValue('PLUGLIN_ID_FOLDERS', serialize($folders));
        self::getOrganization();

        return null;
    }

    public static function getProject()
    {
        $api = new PluglinClient();

        $id_project = (int) Configuration::get('PLUGLIN_PROJECT');
        $token_blarlo = Configuration::get('PLUGLIN_TOKEN');

        $data = [];
        $data['token'] = $token_blarlo;
        $data['id'] = $id_project;
        $api->__call('getProject', $data);

        $response = $api->last_response;
        if (is_array($response)) {
            if (isset($response['id']) && (int) $response['id']) {
                $id_langs_selected = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));
                self::updateLanguagesData($id_langs_selected, $response['destinations']);
            } else {
                // TODO Gestión de error

                echo 'ERROR ';
                echo 'Consulta:';
                Tools::dieObject($api->last_request, 0);
                echo 'Respuesta Estructurada';
                Tools::dieObject($api->last_response, 0);
                echo 'Respuesta Raw';
                Tools::dieObject($api->last_response_raw, 0);
                echo 'Error';
                Tools::dieObject($api->last_error, 0);
                die();
            }
        } else {
            // TODO Gestión de error

            echo 'ERROR ';
            echo 'Consulta:';
            Tools::dieObject($api->last_request, 0);
            echo 'Respuesta Estructurada';
            Tools::dieObject($api->last_response, 0);
            echo 'Respuesta Raw';
            Tools::dieObject($api->last_response_raw, 0);
            echo 'Error';
            Tools::dieObject($api->last_error, 0);
            die();
        }
    }

    public static function updateProject()
    {
        if (!(int) Configuration::get('PLUGLIN_PROJECT')) {
            return self::openProject();
        }

        $api = new PluglinClient();
        list($selectedLangsIDs, $data) = self::prepareProjectData();

        $api->__call('updProject', $data);

        $response = $api->last_response;

        if (!is_array($response)) {
            return [['message' => 'Not Connected']];
        }

        if (!isset($response['id']) || !(int) $response['id']) {
            return [['message' => $response['message']]];
        }

        self::updateLanguagesData($selectedLangsIDs, $response['destinations']);
        self::getOrganization();

        return null;
    }

    public static function sendSupport($message)
    {
        $api = new PluglinClient();
        $token = Configuration::get('PLUGLIN_TOKEN');

        $data = [];
        $data['token'] = $token;
        $data['message'] = $message;

        $api->__call('support', $data);

        $response = $api->last_response;

        if (is_array($response) && isset($response['message'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function closeProject()
    {
        if (!(int) Configuration::get('PLUGLIN_PROJECT')) {
            return false;
        }

        $api = new PluglinClient();
        $token_blarlo = Configuration::get('PLUGLIN_TOKEN');

        $data = [];
        $data['id'] = (int) Configuration::get('PLUGLIN_PROJECT');
        $data['token'] = $token_blarlo;
        $api->__call('delProject', $data);

        if (is_array($api->last_response)) {
            // BORRAMOS EL ID DEL PROYECTO DE PLUGLIN
            Configuration::deleteByName('PLUGLIN_PROJECT');
            Configuration::deleteByName('PLUGLIN_SELECT_LANGUAGES');
        } else {
            // TODO Gestión de error
            echo 'ERROR ';
            echo 'Consulta:';
            Tools::dieObject($api->last_request, 0);
            echo 'Respuesta Estructurada';
            Tools::dieObject($api->last_response, 0);
            echo 'Respuesta Raw';
            Tools::dieObject($api->last_response_raw, 0);
            echo 'Error';
            Tools::dieObject($api->last_error, 0);
            die();
        }

        return true;
    }

    public static function getOrganization()
    {
        $api = new PluglinClient();
        $data = [];
        $data['token'] = Configuration::get('PLUGLIN_TOKEN');

        $response = $api->__call('organization', $data);
        Configuration::updateValue('PLUGLIN_ORGANIZATION', serialize(json_decode($response['message'])));
    }

    public static function createFolder($name_folder)
    {
        if (!(int) Configuration::get('PLUGLIN_PROJECT')) {
            return false;
        }

        $api = new PluglinClient();
        $token_blarlo = Configuration::get('PLUGLIN_TOKEN');

        $data = [];
        $data['project'] = (int) Configuration::get('PLUGLIN_PROJECT');
        $data['token'] = $token_blarlo;
        $data['name'] = $name_folder;
        $api->__call('postContent', $data);

        $response = $api->last_response;
        if (is_array($response) && isset($response['id']) && (int) $response['id']) {
            return (int) $response['id'];
        }

        // TODO Gestión de error
        echo 'ERROR ';
        echo 'Consulta:';
        Tools::dieObject($api->last_request, 0);
        echo 'Respuesta Estructurada';
        Tools::dieObject($api->last_response, 0);
        echo 'Respuesta Raw';
        Tools::dieObject($api->last_response_raw, 0);
        echo 'Error';
        Tools::dieObject($api->last_error, 0);
        die();
    }

    private static function updateLanguagesData($selectedLangsIDs, $destinations)
    {
        foreach ($selectedLangsIDs as $key => $lang) {
            foreach ($destinations as $lang_target) {
                if ($lang['code'] == $lang_target['code']) {
                    $selectedLangsIDs[$key]['translated'] = $lang_target['translated'];
                    $selectedLangsIDs[$key]['reviewed'] = $lang_target['reviewed'];
                    break;
                }
            }
        }
        Configuration::updateValue('PLUGLIN_SELECT_LANGUAGES', serialize($selectedLangsIDs));
    }

    private static function prepareProjectData()
    {
        $defaultLangID = (int) Configuration::get('PS_LANG_DEFAULT');
        $selectedLangsIDs = unserialize(Configuration::get('PLUGLIN_SELECT_LANGUAGES'));

        $source = Language::getIsoById($defaultLangID);
        $destinations = [];
        foreach ($selectedLangsIDs as $id_lang) {
            $destinations[] = $id_lang['code'];
        }

        $token = Configuration::get('PLUGLIN_TOKEN');
        $access_token = Configuration::get('PLUGLIN_TOKEN_ACCESS');
        $url_sync = Context::getContext()->link->getModuleLink('pluglin', 'cron').
            '?token='.$access_token.'&method=sync';
        $automatic_sync = (int) Configuration::get('PLUGLIN_AUTOMATIC_SYNC');
        $frequency_sync = (int) Configuration::get('PLUGLIN_FREQUENCY_SYNC');
        $projectID = Configuration::get('PLUGLIN_PROJECT');

        $data = [];
        if ((int) $projectID) {
            $data['id'] = $projectID;
        }
        $data['token'] = $token;
        $data['name'] = Configuration::get('PS_SHOP_NAME');
        $data['integration'] = 'prestashop';
        $data['source'] = $source;
        $data['destinations'] = $destinations;
        $data['autoSync'] = $automatic_sync ? $frequency_sync : 0;
        $data['autoSyncWebhook'] = $url_sync;

        return [$selectedLangsIDs, $data];
    }
}
