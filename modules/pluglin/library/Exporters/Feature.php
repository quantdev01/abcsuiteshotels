<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Feature implements Exporter
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
        $featureID = (int) $row['id_content'];
        $feature = new \Feature($featureID, $this->originLangID);

        $data = [
            'name' => $feature->name,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'f-'.str_pad($featureID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'name' => $feature->name,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedFeature = new \Feature($featureID, $langObj->id);

            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'name' => $translatedFeature->name,
                ],
            ];
        }

        return $data;
    }
}
