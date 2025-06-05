<?php

namespace Pluglin\Prestashop\Exporters;

class Attribute implements \Pluglin\Prestashop\Contracts\Exporter
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
        $attributeID = (int) $row['id_content'];
        $attribute = new \Attribute($attributeID, $this->originLangID);

        $data = [
            'name' => $attribute->name,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'a-'.str_pad($attributeID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'name' => $attribute->name,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedAttribute = new \Attribute($attributeID, $langObj->id);

            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'name' => $translatedAttribute->name,
                ],
            ];
        }

        return $data;
    }
}
