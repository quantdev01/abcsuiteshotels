<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class Product implements Exporter
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
        $productID = (int) $row['id_content'];
        $product = new \ProductCore($productID, false, $this->originLangID);

        $data = [
            'name' => $product->name,
            'type' => 'file',
            'parent' => $this->folders[$row['type']],
            'external_key' => 'p-'.str_pad($productID, 6, '0', STR_PAD_LEFT),
            'fields' => [
                'name' => $product->name,
                'description' => $product->description,
                'description_short' => $product->description_short,
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'meta_keywords' => $product->meta_keywords,
                'available_now' => $product->available_now,
                'available_later' => $product->available_later,
                'delivery_in_stock' => $product->delivery_in_stock ?? '',
                'delivery_out_stock' => $product->delivery_out_stock ?? '',
            ],
            'translations' => [],
        ];

        if ($row['id_pluglin']) {
            $data['id'] = $row['id_pluglin'];
        }

        foreach ($this->destinationIDs as $destinationID) {
            $langObj = new \LanguageCore($destinationID);
            $translatedProduct = new \Product($productID, false, $langObj->id);
            $data['translations'][] = [
                'code' => $langObj->iso_code,
                'fields' => [
                    'name' => $translatedProduct->name,
                    'description' => $translatedProduct->description,
                    'description_short' => $translatedProduct->description_short,
                    'meta_title' => $translatedProduct->meta_title,
                    'meta_description' => $translatedProduct->meta_description,
                    'meta_keywords' => $translatedProduct->meta_keywords,
                    'available_now' => $translatedProduct->available_now,
                    'available_later' => $translatedProduct->available_later,
                    'delivery_in_stock' => $translatedProduct->delivery_in_stock ?? '',
                    'delivery_out_stock' => $translatedProduct->delivery_out_stock ?? '',
                ],
            ];
        }

        return $data;
    }
}
