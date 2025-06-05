<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Contracts\Exporter;

class ExporterFactory
{
    private $destinationIDs;
    private $folders;

    public function __construct()
    {
        $this->destinationIDs = array_keys(unserialize(\Configuration::get('PLUGLIN_SELECT_LANGUAGES')));
        $this->folders = unserialize(\Configuration::get('PLUGLIN_ID_FOLDERS'));
    }

    public function getExporter(string $type): Exporter
    {
        switch ($type) {
            case 'attribute':
                return new Attribute($this->destinationIDs, $this->folders);
            case 'attribute_group':
                return new AttributeGroup($this->destinationIDs, $this->folders);
            case 'category':
                return new Category($this->destinationIDs, $this->folders);
            case 'cms':
                return new CMS($this->destinationIDs, $this->folders);
            case 'database':
                return new Database($this->destinationIDs, $this->folders);
            case 'email':
                return new Email($this->destinationIDs, $this->folders);
            case 'feature':
                return new Feature($this->destinationIDs, $this->folders);
            case 'feature_value':
                return new FeatureValue($this->destinationIDs, $this->folders);
            case 'manufacturer':
                return new Manufacturer($this->destinationIDs, $this->folders);
            case 'product':
                return new Product($this->destinationIDs, $this->folders);
            case 'supplier':
                return new Supplier($this->destinationIDs, $this->folders);
            case 'theme':
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    return new Theme16($this->destinationIDs, $this->folders);
                }

                return new Theme($this->destinationIDs, $this->folders);
            default:
                return new UnexpectedType($this->destinationIDs, $this->folders);
        }
    }
}
