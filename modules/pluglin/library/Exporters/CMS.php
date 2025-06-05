<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class CMS implements Exporter
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
        $cmsID = (int) $row['id_content'];
        $cmsEntry = new \CMS($cmsID, $this->originLangID);
        $data = [
            'name' => $cmsEntry->meta_title,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'cms-'.str_pad($cmsID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'content' => $cmsEntry->content,
                'head_seo_title' => $cmsEntry->head_seo_title ?? '',
                'meta_title' => $cmsEntry->meta_title,
                'meta_description' => $cmsEntry->meta_description,
                'meta_keywords' => $cmsEntry->meta_keywords,
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedCMSEntry = new \CMS($cmsID, $langObj->id);
            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'content' => $translatedCMSEntry->content,
                    'head_seo_title' => $translatedCMSEntry->head_seo_title ?? '',
                    'meta_title' => $translatedCMSEntry->meta_title,
                    'meta_description' => $translatedCMSEntry->meta_description,
                    'meta_keywords' => $translatedCMSEntry->meta_keywords,
                ],
            ];
        }

        return $data;
    }
}
