<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Supplier implements Exporter
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
        $supplierID = (int) $row['id_content'];
        $supplier = new \Supplier($supplierID, false, $this->originLangID);

        $data = [
            'name' => $supplier->name,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 's-'.str_pad($supplierID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'description' => $supplier->description,
                'meta_title' => $supplier->meta_title,
                'meta_description' => $supplier->meta_description,
                'meta_keywords' => $supplier->meta_keywords,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedSupplier = new \Supplier($supplierID, false, $langObj->id);
            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'description' => $translatedSupplier->description,
                    'meta_title' => $translatedSupplier->meta_title,
                    'meta_description' => $translatedSupplier->meta_description,
                    'meta_keywords' => $translatedSupplier->meta_keywords,
                ],
            ];
        }

        return $data;
    }
}
