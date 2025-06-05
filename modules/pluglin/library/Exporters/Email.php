<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Email implements Exporter
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
        $defaultLangISO = \Language::getIsoById((int) $this->originLangID);
        $extra = json_decode($row['data_json']);
        if (empty($extra)) {
            return null;
        }

        if (!isset($extra->file)) {
            return null;
        }

        $filename = basename($extra->file);

        $data = [
            'name' => $filename,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'EMAIL-'.$row['id_content'].'-'.$filename,
            'fields' => [
                'body' => preg_replace("/\r|\n/", '', Tools::file_get_contents($extra->path)),
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $emailTranslationFilePath = str_replace(
                '/'.$defaultLangISO.'/',
                '/'.$langObj->iso_code.'/',
                $extra->path
            );

            //Create base objects
            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'body' => file_exists($emailTranslationFilePath) ?
                        preg_replace("/\r|\n/", '', Tools::file_get_contents($emailTranslationFilePath)) :
                        '',
                ],
            ];
        }

        return $data;
    }
}
