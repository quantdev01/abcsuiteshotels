<?php

namespace Pluglin\Prestashop\Exporters;

class AttributeGroup implements \Pluglin\Prestashop\Contracts\Exporter
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
        $attributeGroupID = (int) $row['id_content'];
        $attributeGroup = new \AttributeGroup($attributeGroupID, $this->originLangID);

        $data = [
            'name' => $attributeGroup->name,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'ag-'.str_pad($attributeGroupID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'name' => $attributeGroup->name,
                'public_name' => $attributeGroup->public_name,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedAttributeGroup = new \AttributeGroup($attributeGroupID, $langObj->id);

            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'name' => $translatedAttributeGroup->name,
                    'public_name' => $translatedAttributeGroup->public_name,
                ],
            ];
        }

        return $data;
    }
}
