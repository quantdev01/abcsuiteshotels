<?php

namespace Pluglin\Prestashop\Exporters;

use Pluglin\Prestashop\Services\PluglinLogger;

class UnexpectedType implements \Pluglin\Prestashop\Contracts\Exporter
{
    private $destinationIDs;
    private $folders;

    public function __construct(array $destinationIDs, array $folders)
    {
        $this->destinationIDs = $destinationIDs;
        $this->folders = $folders;
    }

    public function getData(array $row): ?array
    {
        PluglinLogger::error('Unexpected row type', [
            'row' => $row,
            'destinationIDs' => $this->destinationIDs,
            'folders' => $this->folders,
        ]);

        return null;
    }
}
