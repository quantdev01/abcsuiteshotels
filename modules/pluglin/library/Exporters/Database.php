<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Database implements Exporter
{
    private $destinationIDs;
    private $folders;
    private $originLangID;

    public function __construct(array $destinationIDs, array $folders)
    {
        $this->destinationIDs = $destinationIDs;
        $this->folders = $folders;
        $this->originLangID = \Configuration::get('PS_LANG_DEFAULT');
    }

    public function getData(array $row): ?array
    {
        $extra = json_decode($row['data_json']);
        $keysCombination = implode('-', $extra->keys);

        $data = [
            'name' => $extra->table.$keysCombination,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'DB-'.$row['id_content'].'-'.$extra->table.$keysCombination,
            'fields' => [],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        $tableRows = \Db::getInstance()->executeS($this->getQuery($extra));

        foreach ($tableRows as $rowData) {
            if ($rowData['id_lang'] === $this->originLangID) {
                foreach ($extra->translatableColumns as $field) {
                    $data['fields'][$field] = $rowData[$field];
                }
                continue;
            }

            $translation = [
                 'code' => \Language::getIsoById((int) $rowData['id_lang']),
                 'fields' => [],
            ];

            foreach ($extra->translatableColumns as $field) {
                $translation['fields'][$field] = $rowData[$field];
            }

            $data['translations'][] = $translation;
        }

        return $data;
    }

    private function getQuery(\stdClass $extra)
    {
        $query = 'SELECT '.implode(', ', $extra->translatableColumns).', id_lang FROM `'.$extra->table.'` WHERE ';

        foreach ($extra->keysColumns as $index => $colName) {
            // we are querying for all the languages at the end
            if ('id_lang' === $colName) {
                continue;
            }

            if (isset($multipleItems)) {
                $query .= ' AND ';
            }
            $query .= $colName.' = "'.$extra->keys[$index].'"';
            $multipleItems = true;
        }

        $query .= ' AND id_lang IN('.implode(', ', $this->destinationIDs).", {$this->originLangID})";

        return $query;
    }
}
