<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Theme implements Exporter
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

        if (!isset($extra->domain)) {
            return null;
        }

        $originText = base64_decode($extra->original_ps);

        $data = [
            'name' => $originText,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'THEME-'.$row['id_content'].'-'.md5($extra->original_ps),
            'translations' => [],
            'fields' => [
                'content' => $originText,
            ],
        ];

        if (!empty($row['id_pluglin'])) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translator = \Context::getContext()->getTranslatorFromLocale($langObj->locale)->getCatalogue();
            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'content' => $translator->get($originText, $extra->domain),
                ],
            ];
        }

        return $data;
    }
}
