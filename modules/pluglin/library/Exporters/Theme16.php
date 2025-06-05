<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Theme16 implements Exporter
{
    /** @var int[] */
    private $destinationIDs;
    private $folders;

    public function __construct(array $destinationIDs, array $folders)
    {
        // We only need the IDS at this point, so we get only the keys
        $this->destinationIDs = $destinationIDs;
        $this->folders = $folders;
    }

    public function getData(array $row): ?array
    {
        $extra = json_decode($row['data_json']);
        if (empty($extra)) {
            return null;
        }

        if (!isset($extra->file)) {
            return null;
        }

        $filename = str_replace('.php', '', basename($extra->file));

        $data = [
            'name' => $filename,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'THEME-'.$row['id_content'].'-'.md5($filename),
            'translations' => [],
            'fields' => [],
        ];

        global $_LANG;
        if (file_exists($extra->file)) {
            require $extra->file;
        }

        foreach ($_LANG as $key => $phrase) {
            if ($key == $extra->key) {
                $data['fields'][] = [
                    'content' => $phrase,
                ];
            }
        }

        foreach ($this->destinationIDs as $destinationID) {
            if (!file_exists($extra->file)) {
                continue;
            }

            $langObj = new \LanguageCore($destinationID);
            $path = $extra->basePath.$langObj->iso_code.'.php';
            if ('field' == $extra->type) {
                $path = $extra->basePath.$langObj->iso_code.'/fields.php';
            }

            if (!file_exists($path)) {
                continue;
            }

            $translation = [
                'code' => $langObj->iso_code,
                'fields' => [],
            ];

            $_LANG = [];
            require $path;
            foreach ($_LANG as $key => $phrase) {
                if ($key == $extra->key) {
                    $translation['fields'][] = [
                        'content' => $phrase,
                    ];
                }
            }

            $data['translations'][] = $translation;
        }

        return $data;
    }
}
