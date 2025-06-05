<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Category implements Exporter
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
        $itemID = (int) $row['id_content'];
        $category = new \CategoryCore($itemID, $this->originLangID);

        $data = [
            'name' => $category->name,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'c-'.str_pad($itemID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'name' => $category->name,
                'description' => $category->description,
                'meta_title' => $category->meta_title,
                'meta_description' => $category->meta_description,
                'meta_keywords' => $category->meta_keywords,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $categoryTranslation = new \Category($itemID, $langObj->id);
            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'name' => $categoryTranslation->name,
                    'description' => $categoryTranslation->description,
                    'meta_title' => $categoryTranslation->meta_title,
                    'meta_description' => $categoryTranslation->meta_description,
                    'meta_keywords' => $categoryTranslation->meta_keywords,
                ],
            ];
        }

        return $data;
    }
}
