<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class FeatureValue implements Exporter
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
        $featureValueID = (int) $row['id_content'];
        $featureValue = new \FeatureValue($featureValueID, $this->originLangID);

        $data = [
            'name' => $featureValue->name ?? $featureValue->value,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'fv-'.str_pad($featureValueID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'value' => $featureValue->value,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedFeature = new \FeatureValue($featureValueID, $langObj->id);

            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'value' => $translatedFeature->value,
                ],
            ];
        }

        return $data;
    }
}
