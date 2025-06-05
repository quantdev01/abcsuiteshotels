<?php

namespace Pluglin\Prestashop\Extractors;

use Db;
use Pluglin\Prestashop\Services\Counter;

class Database
{
    /** @var string[] */
    private $excludedTables = [
        _DB_PREFIX_.'product_lang',
        _DB_PREFIX_.'attribute_lang',
        _DB_PREFIX_.'attribute_group_lang',
        _DB_PREFIX_.'category_lang',
        _DB_PREFIX_.'cms_category_lang',
        _DB_PREFIX_.'configuration_kpi_lang',
        _DB_PREFIX_.'configuration_lang',
        _DB_PREFIX_.'feature_lang',
        _DB_PREFIX_.'feature_value_lang',
        _DB_PREFIX_.'layered_indexable_attribute_group_lang_value',
        _DB_PREFIX_.'layered_indexable_attribute_lang_value',
        _DB_PREFIX_.'layered_indexable_feature_lang_value',
        _DB_PREFIX_.'layered_indexable_feature_value_lang_value',
        _DB_PREFIX_.'manufacturer_lang',
        _DB_PREFIX_.'cms_lang',
        _DB_PREFIX_.'advice_lang',
        _DB_PREFIX_.'badge_lang',
        _DB_PREFIX_.'ps_cms_role_lang',
        _DB_PREFIX_.'cart_rule_lang',
        _DB_PREFIX_.'group_lang',
        _DB_PREFIX_.'lang',
        _DB_PREFIX_.'lang_shop',
        _DB_PREFIX_.'profile_lang',
        _DB_PREFIX_.'quick_access_lang',
        _DB_PREFIX_.'supplier_lang',
        _DB_PREFIX_.'tab_lang',
    ];

    /** @var string[] */
    private $tables = [];

    /** @var int */
    private $wordCount = 0;

    /** @var int */
    private $originID;

    /** @param int $originID Represents the ID for the origin language (lang_id) */
    public function __construct(int $originID = 1)
    {
        $this->originID = $originID;
    }

    public function extractData()
    {
        $this->getTables();

        /** @var Db $dbInstance */
        $dbInstance = DB::getInstance();
        // gets the translatable fields from DB
        $translatableFields = [];
        foreach ($this->tables as $table) {
            // get the inner rows of the table
            $fields = $dbInstance->executeS('DESCRIBE '.$table);

            // filter out the tables that don't contain a language ID
            if (false === array_search('id_lang', array_column($fields, 'Field'))) {
                continue;
            }

            // Something has created a key with an index, we need to get all the id_* as ids
            if (0 === array_search('MUL', array_column($fields, 'Key'))) {
                $translatableFields[$table]['id'] = array_filter(array_column($fields, 'Field'), function ($colName) {
                    return false !== strpos($colName, 'id_');
                });
            }

            foreach ($fields as $field) {
                // Find the id of the table
                if ('PRI' === $field['Key']) {
                    $translatableFields[$table]['id'][] = $field['Field'];
                    continue;
                }

                // find the translatable content of the table
                if ('varchar' === substr($field['Type'], 0, strlen('varchar')) || 'text' === $field['Type']) {
                    $translatableFields[$table]['content'][] = $field['Field'];
                }
            }
        }

        // **BUG**: El contador que se usa para generar las ids de contenidos va iterando por las tablas -> rows -> fields,
        // con lo que, si desaparece una de las rows por en medio, el resto de contadores cambian y dejan de estar en
        // sincronía con las posibles "pluglin_id" que tengamos almacenadas.
        $count = 0;
        $wordsCounter = new Counter();
        foreach ($translatableFields as $table => $fields) {
            if (!array_key_exists('id', $fields)) {
                continue;
            }

            $sql = sprintf(
                'SELECT %s, %s FROM %s WHERE id_lang IN (%s)',
                implode(', ', $fields['id']),
                implode(', ', $fields['content']),
                $table,
                $this->originID
            );

            $rows = $dbInstance->executeS($sql);

            foreach ($rows as $row) {
                // Add the words to the count
                foreach ($fields['content'] as $content) {
                    $wordsCounter->addWords($row[$content]);
                }

                // Extract the id numbers of the columns that are considered IDs for this row
                $keys = array_map(function ($colName, $colVal) use ($fields) {
                    return in_array($colName, $fields['id']) ? $colVal : null;
                }, array_keys($row), $row);

                // Right now it's inserting the data for each iteration of the row, we tried inserting an array of contents
                // but it didn't work at all, Inserting for each row has the advantage of reducing memory footprint anyway
                // which might be important in small prestashop installations.
                // This also forces a replace of matching IDs, it works because this information is never updated during
                // the life of the plugin installation. The second we pay any attention to the ids, or we take into account
                // updates, we need to check how to not replace them.
                $dbInstance->insert(
                    'pluglin_content',
                    [
                    'type' => 'database',
                    'id_content' => $count++,
                    'data_json' => \pSQL(str_replace("'", "\'", json_encode([
                        'table' => $table,
                        'id_lang' => (int) $row['id_lang'],
                        'keysColumns' => $fields['id'],
                        'keys' => array_filter(array_values($keys)),
                        'translatableColumns' => $fields['content'],
                    ]))),
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_update' => date('Y-m-d H:i:s'),
                ],
                    false,
                    false,
                    Db::REPLACE
                );
            }
        }

        $this->wordCount = $wordsCounter->getCurrentCount();
    }

    public function getWordCount(): int
    {
        return $this->wordCount;
    }

    private function getTables(): void
    {
        if (!empty($this->tables)) {
            return;
        }

        $allTables = Db::getInstance()->executeS('SHOW TABLES');

        foreach ($allTables as $table) {
            $tableName = reset($table);

            // filter the tables that are not from this PS installation
            if (_DB_PREFIX_ !== substr($tableName, 0, strlen(_DB_PREFIX_))) {
                continue;
            }

            // filter out tables that don't have _lang suffix
            if (false === strpos($tableName, '_lang')) {
                continue;
            }

            // filter out the ones that are in the excluded tables list
            if (in_array($tableName, $this->excludedTables)) {
                continue;
            }

            $this->tables[] = $tableName;
        }
    }
}
