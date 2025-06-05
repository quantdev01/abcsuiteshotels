<?php

namespace Pluglin\Prestashop\Contracts;

interface Exporter
{
    public function __construct(array $destinationIDs, array $folders);

    public function getData(array $row): ?array;
}
