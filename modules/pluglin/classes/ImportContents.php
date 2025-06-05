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
class ImportContents
{
    private $filename;
    private $wordCount;
    public $module_name = 'pluglin';
    private $sfKernel;

    public function importData($id_datapack, $url)
    {
        $this->download($id_datapack, $url);
        $this->processData();

        // this gets overwritten by "getProject" but in case that call takes
        // a little longer or fails, it's kept as a fallback
        Configuration::updateValue('PLUGLIN_TOTAL_WORDS', $this->wordCount);

        // Updates the percentage of translations and the word count
        PluglinTools::getProject();
        $this->clearCache();
    }

    /**
     * This function will bootstrap the symfony kernel.
     *
     * @return false|null
     */
    protected function getSfKernel()
    {
        if (null != $this->sfKernel) {
            return $this->sfKernel;
        }

        // Not, ready, boot the kernel
        require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
        $this->sfKernel = new \AppKernel('prod', false);
        $this->sfKernel->boot();

        if (!(null !== $this->sfKernel && $this->sfKernel instanceof \Symfony\Component\HttpKernel\KernelInterface)) {
            PrestaShopLogger::addLog(
                'Pluglin error: Error Not Kernel Translator.',
                4,
                null,
                'ImportContents',
                null,
                true
            );

            return false;
        }

        return $this->sfKernel;
    }

    private function download($dataPackID, $url)
    {
        $fileName = "$dataPackID.jsonl";

        $downloadPath = _PS_MODULE_DIR_.'pluglin'.DIRECTORY_SEPARATOR.'files'.
            DIRECTORY_SEPARATOR.'download'.DIRECTORY_SEPARATOR;
        $downloadFilePath = $downloadPath.$fileName;

        if (!file_exists($downloadPath)) {
            mkdir($downloadPath, 0777, true);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        $data = curl_exec($ch);

        if (false === $data) {
            // TODO: Curl has failed. Log curl_err($ch) somewhere
            return;
        }

        curl_close($ch);
        $fileResource = fopen($downloadFilePath, 'w+');
        fwrite($fileResource, $data);
        fclose($fileResource);

        $this->filename = $downloadFilePath;
    }

    private function processData()
    {
        $fp = fopen($this->filename, 'r');

        if (!$fp) {
            // fopen returns false when it can't read the file
            // TODO: Log the error
            return;
        }

        while (!feof($fp)) {
            $linea = fgets($fp);

            if (empty($linea)) {
                continue;
            }

            $json_decoded = json_decode($linea, 1);
            if ('file' == $json_decoded['type']) {
                $this->setDetail($json_decoded);
            }
        }

        fclose($fp);
    }

    private function setDetail($row)
    {
        list($type, $id) = explode('-', $row['external_key']);

        $data = false;
        switch ($type) {
            case 'c': // Category
                $data = $this->setDetailItem($row, 'category_lang', 'id_category', $id);
                $this->updateKey('category', $id, $row['id']);
                break;
            case 'p': // Product
                $data = $this->setDetailItem($row, 'product_lang', 'id_product', $id);
                $this->updateKey('product', $id, $row['id']);
                break;
            case 'a': // Attribute
                $data = $this->setDetailItem($row, 'attribute_lang', 'id_attribute', $id);
                $this->updateKey('attribute', $id, $row['id']);
                break;
            case 'ag': // Attribute_group
                $data = $this->setDetailItem($row, 'attribute_group_lang', 'id_attribute_group', $id);
                $this->updateKey('attribute_group', $id, $row['id']);
                break;
            case 'f': // Feature
                $data = $this->setDetailItem($row, 'feature_lang', 'id_feature', $id);
                $this->updateKey('feature', $id, $row['id']);
                break;
            case 'fv': // Feature Value
                $data = $this->setDetailItem($row, 'feature_value_lang', 'id_feature_value', $id);
                $this->updateKey('feature_value', $id, $row['id']);
                break;
            case 's': // Supplier
                $data = $this->setDetailItem($row, 'supplier_lang', 'id_supplier', $id);
                $this->updateKey('supplier', $id, $row['id']);
                break;
            case 'm': // Manufacturer
                $data = $this->setDetailItem($row, 'manufacturer_lang', 'id_manufacturer', $id);
                $this->updateKey('manufacturer', $id, $row['id']);
                break;
            case 'cms': // Cms
                $data = $this->setDetailItem($row, 'cms_lang', 'id_cms', $id);
                $this->updateKey('cms', $id, $row['id']);
                break;
            case 'EMAIL': // Email
                $data = $this->setEmailData($row, $id);
                $this->updateKey('email', $id, $row['id']);
                break;
            case 'DB': // Database
                $data = $this->setDatabaseDataUpdate($row, $id);
                $this->updateKey('database', $id, $row['id']);
                break;
            case 'THEME': // Theme
                //Is a 1.6.x?
                if ((bool) version_compare(_PS_VERSION_, '1.7', '<')) {
                    $data = $this->setThemeData16($row, $id);
                } else {
                    $data = $this->setThemeData17($row, $id);
                }
                $this->updateKey('theme', $id, $row['id']);
                break;
        }

        // add the count
        $this->wordCount += $row['words'];

        return $data;
    }

    private function setThemeData17($row, $id)
    {
        $translations = $row['translations'];
        foreach ($translations as $trans) {
            $destinationLangID = LanguageCore::getIdByIso($trans['code']);
            $destinationLangEntity = new LanguageCore((int) $destinationLangID);

            $origRow = Db::getInstance()
                ->getRow(
                    '
                        SELECT *
                        
                        FROM `'._DB_PREFIX_.'pluglin_content`

                        WHERE id_content = "'.$id.'"
                            AND `type` = "theme"
                    '
                );

            //Check if the row exists in the database
            if (count($origRow) > 0) {
                $extra = json_decode($origRow['data_json']);
                //must be ps original translation english
                $translations_ = [
                    [
                        'default' => base64_decode($extra->original_ps),
                        'domain' => $extra->domain,
                        'edited' => $trans['fields']['content'],
                        'locale' => $destinationLangEntity->locale,
                        'theme' => ($extra->is_theme) ? Context::getContext()->shop->theme->getName() :
                        null,
                    ],
                ];

                try {
                    $translationService = $this->getSfKernel()->getContainer()->get('prestashop.service.translation');

                    foreach ($translations_ as $translation_) {
                        if (empty($translation_['theme'])) {
                            $translation_['theme'] = null;
                        }

                        try {
                            $lang = $translationService->findLanguageByLocale($translation_['locale']);
                        } catch (Exception $exception) {
                            //throw new BadRequestHttpException($exception->getMessage());
                            PrestaShopLogger::addLog(
                                'Pluglin error: Error Localo Translator not exist.'.$exception->getMessage(),
                                1,
                                null,
                                'ImportContents',
                                null,
                                true
                            );

                            return false;
                        }

                        $translationService->saveTranslationMessage(
                            $lang,
                            $translation_['domain'],
                            $translation_['default'],
                            $translation_['edited'],
                            $translation_['theme']
                        );
                    }

                    return true;
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog(
                        'Pluglin error: Error saving Translator theme item.',
                        4,
                        null,
                        json_encode(['row' => $row])
                    );

                    return false;
                }
            }
        }
    }

    private function updateKey($type, $id_item, $id_pluglin)
    {
        $data = [];
        $data['id_pluglin'] = (int) $id_pluglin;
        $data['date_update'] = date('Y-m-d H:i:s');

        Db::getInstance()->update('pluglin_content', $data, "type='".$type."' AND id_content=".$id_item);
    }

    /**
     * Re-update, into the database, the database fields.
     *
     * @param $row
     * @param $id_field
     */
    private function setDatabaseDataUpdate($row, $id_field)
    {
        //must be update fields because we save one translate by row
        $translations = $row['translations'];

        $origRow = Db::getInstance()
            ->getRow(
                '
                        SELECT *
                        
                        FROM `'._DB_PREFIX_.'pluglin_content`

                        WHERE id_content = "'.$id_field.'"
                            AND `type` = "database"
                    '
            );

        //Check if the row exists in the database
        if (count($origRow) > 0) {
            $extra = json_decode($origRow['data_json']);
        } else {
            return false;
        }

        //Iterate the translations
        foreach ($translations as $translation) {
            //First, build the lang ID
            $destinyLangID = LanguageCore::getIdByIso($translation['code']);

            //Build the query
            $sql = 'UPDATE `'.$extra->table.'` SET ';
            $key_column_id_lang = null;

            //Surround the SQL creation with Try/catch
            try {
                //Build the, update params
                $isFirst = true;

                //Then the data ones
                foreach ($extra->translatableColumns as $column) {
                    if (!$isFirst) {
                        $sql .= ',';
                    }
                    $sql .= '`'.pSQL($column).'`="'.pSQL($translation['fields'][$column]).'"';
                    $isFirst = false;
                }

                //build where params
                $isFirst = true;
                foreach ($extra->keysColumns as $keyCol => $value) {
                    if (!$isFirst) {
                        $sql .= ' AND ';
                    } else {
                        $sql .= ' WHERE ';
                    }
                    if (isset($key_column_id_lang) &&
                        null != $key_column_id_lang &&
                        $key_column_id_lang == $keyCol
                        ) {
                        //id_lang, change it
                        $sql .= '`'.pSQL($value).'`="'.pSQL($destinyLangID).'"';
                    } else {
                        //not id, lang, insert it
                        $sql .= '`'.pSQL($value).'`="'.pSQL($extra->keys[$keyCol]).'"';
                    }
                    $isFirst = false;
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'Pluglin error: creating SQL ERROR.',
                    4,
                    null,
                    json_encode(['row' => $row, 'sql' => $sql])
                );

                return false;
            }

            //Execute the prepared SQL
            $res = false;
            try {
                $res = Db::getInstance()->execute($sql);
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'Pluglin error: updating database, SQL ERROR.',
                    4,
                    null,
                    json_encode(['row' => $row, 'sql' => $sql])
                );
            }
        }

        return $res;
    }

    /**
     * Re-inserts, into the database, the database fields.
     *
     * @param $row
     * @param $id_field
     */
    private function setDatabaseData($row, $id_field)
    {
        $translations = $row['translations'];

        $origRow = Db::getInstance()
            ->getRow(
                '
                        SELECT *
                        
                        FROM `'._DB_PREFIX_.'pluglin_content`

                        WHERE id_content = "'.$id_field.'"
                            AND `type` = "database"
                    '
            );

        //Check if the row exists in the database
        if (count($origRow) > 0) {
            $extra = json_decode($origRow['data_json']);
        } else {
            return false;
        }

        //Iterate the translations
        foreach ($translations as $translation) {
            //First, build the lang ID
            $destinyLangID = LanguageCore::getIdByIso($translation['code']);
            $key_column_id_lang = null;

            //Build the query
            $sql = 'INSERT INTO `'.$extra->table.'` (';

            //Surround the SQL creation with Try/catch
            try {
                //Build the, to-insert params
                $isFirst = true;
                foreach ($extra->keysColumns as $key => $column) {
                    if (!$isFirst) {
                        $sql .= ',';
                    }
                    $sql .= '`'.$column.'`';
                    $isFirst = false;

                    if ('id_lang' == $column) {
                        $key_column_id_lang = $key;
                    }
                }

                foreach ($extra->translatableColumns as $column) {
                    if (!$isFirst) {
                        $sql .= ',';
                    }
                    $sql .= '`'.$column.'`';
                }

                //Close the parentheses
                $sql .= ') VALUES (';

                //Now, insert the "datas", first the key ones
                $isFirst = true;
                foreach ($extra->keys as $keyCol => $value) {
                    if (!$isFirst) {
                        $sql .= ',';
                    }
                    if (isset($key_column_id_lang) &&
                        null != $key_column_id_lang &&
                        $key_column_id_lang == $keyCol
                        ) {
                        //id_lang, change it
                        $sql .= '"'.pSQL($destinyLangID).'"';
                    } else {
                        //not id, lang, insert it
                        $sql .= '"'.pSQL($value).'"';
                    }

                    $isFirst = false;
                }

                //Then the data ones
                foreach ($extra->translatableColumns as $keyCol => $column) {
                    if (!$isFirst) {
                        $sql .= ',';
                    }
                    $sql .= '"'.pSQL($translation['fields'][$column]).'"';
                }

                $sql .= ') ON DUPLICATE KEY UPDATE ';

                //Build the on duplicate key update //key not need update
                $isFirst = true;

                //Then the data ones
                foreach ($extra->translatableColumns as $keyCol => $column) {
                    if (!$isFirst) {
                        $sql .= ',';
                    }
                    $sql .= '`'.pSQL($column).'`="'.pSQL($translation['fields'][$column]).'"';
                    $isFirst = false;
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'Pluglin error: creating SQL ERROR.',
                    4,
                    null,
                    json_encode(['row' => $row, 'sql' => $sql])
                );

                return false;
            }

            //Execute the prepared SQL
            $res = false;
            try {
                $res = Db::getInstance()->execute($sql);
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'Pluglin error: updating database, SQL ERROR.',
                    4,
                    null,
                    json_encode(['row' => $row, 'sql' => $sql])
                );
            }
        }

        return $res;
    }

    /**
     * Updates the theme data for Prestashop 1.6.
     *
     * @param $row
     * @param $id_field
     *
     * @return bool
     */
    private function setThemeData16($row, $id)
    {
        $defaultLangISO = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));

        $translations = $row['translations'];
        foreach ($translations as $translation) {
            $origRow = Db::getInstance()
                ->executeS(
                    '
                        SELECT *
                        
                        FROM `'._DB_PREFIX_.'pluglin_content`

                        WHERE id_content = "'.$id.'"
                            AND `type` = "theme"
                    '
                );

            //Check if the row exists in the database
            if (count($origRow) > 0) {
                $origRow = $origRow[0];
                $extra = json_decode($origRow['data_json']);

                //There are two types of theme assets for 1.6, fields and front translations.
                $res = false;
                global $_LANG;

                if ('theme' == $extra->type) {
                    $_LANG = [];

                    //If destiny language file exists, then use the original file
                    if (file_exists($extra->basePath.$translation['code'].'.php')) {
                        require $extra->basePath.$translation['code'].'.php';
                    } else {
                        //If not exists, load the default file translation (Original language)
                        require $extra->basePath.$defaultLangISO.'.php';
                    }
                } else {
                    $_LANG = [];

                    //If destiny language file exists, then use the original file
                    if (file_exists($extra->basePath.$translation['code'].'/fields.php')) {
                        require $extra->basePath.$translation['code'].'/fields.php';
                    } else {
                        //If not exists, load the default file translation (Original language)
                        require $extra->basePath.$defaultLangISO.'/fields.php';
                    }
                }

                //Once required, load the data.
                foreach ($_LANG as $key => $phrase) {
                    if ($key == $extra->key) {
                        //And overwrite the translated keys.
                        $_LANG[$key] = $translation['fields']['content'];
                    }

                    unset($phrase);
                }
                //Build the final PHP file
                $fileContents = $this->build16TranslateFile($_LANG);

                //Then, write to the disk
                if ('theme' == $extra->type) {
                    $res = file_put_contents($extra->basePath.''.$translation['code'].'.php', $fileContents);
                } else {
                    $res = file_put_contents(
                        $extra->basePath.''.$translation['code'].'/fields.php',
                        $fileContents
                    );
                }

                //Store the error if happened
                if (!$res) {
                    PrestaShopLogger::addLog(
                        'Pluglin error: Updating Theme data 16, cannot write.',
                        4,
                        null,
                        json_encode($row)
                    );

                    return false;
                }
            } else {
                PrestaShopLogger::addLog(
                    'Pluglin error: Updating Theme data 16, cannot find origin row.',
                    4,
                    null,
                    json_encode($row)
                );

                return false;
            }
        }

        return true;
    }

    /**
     * This function will create (or overwrite, if exists) the email files.
     *
     * @param $row - The row with the data from the server
     * @param $id_field
     */
    private function setEmailData($row, $id_field)
    {
        $defaultLangISO = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));

        $translations = $row['translations'];
        foreach ($translations as $translation) {
            $origRow = Db::getInstance()
                ->getRow(
                    '
                        SELECT *
                        
                        FROM `'._DB_PREFIX_.'pluglin_content`

                        WHERE id_content = "'.$id_field.'"
                            AND `type` = "email"
                    '
                );
            $filename = null;

            //Check if the row exists in the database
            if (count($origRow) > 0) {
                $extra = json_decode($origRow['data_json']);
                $filename = $extra->path;

                //Replace the language iso code
                $filename = str_replace(
                    '/'.$defaultLangISO.'/',
                    '/'.$translation['code'].'/',
                    $filename
                );

                $real_path = Tools::substr($filename, 0, strrpos($filename, '/'));
            }

            //Write the contents to the files
            if (null != $filename) {
                if (!is_dir($real_path)) {
                    if (!mkdir($real_path, 0755, true)) {
                        //Log the error
                        PrestaShopLogger::addLog(
                            'Pluglin error: Updating Email, cannot create dir.',
                            4,
                            null,
                            json_encode($row)
                        );

                        return false;
                    }
                }
                if (!file_put_contents($filename, $translation['fields']['body'])) {
                    //Log the error
                    PrestaShopLogger::addLog(
                        'Pluglin error: Updating Email, cannot write.',
                        4,
                        null,
                        json_encode($row)
                    );

                    return false;
                }
            } else {
                //Log the error
                PrestaShopLogger::addLog(
                    'Pluglin error: Updating Email, filename is null.',
                    4,
                    null,
                    json_encode($row)
                );

                return false;
            }
        }

        return true;
    }

    private function setDetailItem($row, $table, $id_field, $id_value)
    {
        $translations = $row['translations'];
        $result = true;
        foreach ($translations as $translation) {
            $lang_code = $translation['code'];
            $fields = $translation['fields'];
            $preparedFields = [];

            foreach ($fields as $key => $field) {
                // we don't try adding empty fields
                if (empty($field)) {
                    continue;
                }

                $preparedFields[$key] = pSQL($field, 1);
            }

            $id_lang = (int) Language::getIdByIso($lang_code);
            try {
                $result &= DB::getInstance()->update(
                    $table,
                    $preparedFields,
                    $id_field.' = '.$id_value.' AND id_lang = '.$id_lang
                );
            } catch (\Exception $ex) {
                // Check against exceptions when there are problems with fields.
                PrestaShopLogger::addLog(
                    'Pluglin error: Error SetDetailItem.'.$ex->getMessage(),
                    1,
                    null,
                    'ImportContents',
                    null,
                    true
                );

                return false;
            }
        }

        return $result;
    }

    /**
     * Creates the file structure for a translation.
     *
     * @param $array - The array translations
     */
    private function build16TranslateFile($array)
    {
        $php = '<?php'.PHP_EOL;
        $php .= 'global $_LANG;'.PHP_EOL;
        $php .= '$_LANG = array();'.PHP_EOL;

        foreach ($array as $key => $phrase) {
            $php .= '$_LANG[\''.$key.'\'] = \''.$this->cleanSimpleComma($phrase).'\';'.PHP_EOL;
        }

        //Finish it
        $php .= PHP_EOL.'  return $_LANG; '.PHP_EOL;

        return $php;
    }

    /**
     * Cleans the ' and changes by \'.
     *
     * @param $phrase
     *
     * @return array|string|string[]
     */
    protected function cleanSimpleComma($phrase)
    {
        return str_replace("'", "\'", $phrase);
    }

    protected function clearCache()
    {
        if ((bool) version_compare(_PS_VERSION_, '1.7', '<')) {
            return true;
        } else {
            $this->clearCache17();
        }
    }

    protected function clearCache17()
    {
        $cacheRefresh = $this->getSfKernel()->getContainer()->get('prestashop.cache.refresh');
        try {
            $cacheRefresh->addCacheClear();
            $cacheRefresh->execute();
        } catch (Exception $exception) {
            PrestaShopLogger::addLog(
                'Pluglin error: Error clearing cache for 1.7.'.$exception->getMessage(),
                1,
                null,
                'ImportContents',
                null,
                true
            );
        }
    }

    public function checkOldFiles()
    {
        $module_dir = _PS_MODULE_DIR_.$this->module_name;
        $path = $module_dir.'/files/download/';
        $path_old = $module_dir.'/files/download/old/';
        $files = scandir($path);

        if (!file_exists($path_old)) {
            mkdir($path_old, 0777, true);
        }

        foreach ($files as $file) {
            if (is_dir($path.$file)) {
                continue;
            }

            if (!is_file($path.$file) || 'index.php' == $file) {
                continue;
            }

            if (copy($path.$file, $path_old.$file)) {
                unlink($path.$file);
            }
        }

        return true;
    }
}
