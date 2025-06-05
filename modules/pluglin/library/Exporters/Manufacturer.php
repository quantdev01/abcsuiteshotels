<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Manufacturer implements Exporter
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
        $manufacturerID = (int) $row['id_content'];
        $manufacturer = new \Manufacturer($manufacturerID, false, $this->originLangID);

        $data = [
            'name' => $manufacturer->name,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'm-'.str_pad($manufacturerID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'description' => $manufacturer->description,
                'short_description' => $manufacturer->short_description,
                'meta_title' => $manufacturer->meta_title,
                'meta_description' => $manufacturer->meta_description,
                'meta_keywords' => $manufacturer->meta_keywords,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedManufacturer = new \Manufacturer($manufacturerID, false, $langObj->id);
            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'description' => $translatedManufacturer->description,
                    'short_description' => $translatedManufacturer->short_description,
                    'meta_title' => $translatedManufacturer->meta_title,
                    'meta_description' => $translatedManufacturer->meta_description,
                    'meta_keywords' => $translatedManufacturer->meta_keywords,
                ],
            ];
        }

        return $data;
    }
}
