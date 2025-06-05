<?php

namespace Pluglin\Prestashop\Services;

use Pluglin\Prestashop\Exporters\ExporterFactory;

class Exporter
{
    /** @var string */
    private $filename;

    /** @var false|resource */
    private $file;

    /** @var array */
    private $processedIDs;
    /** @var ExporterFactory */
    private $exporterFactory;

    public function __construct()
    {
        $this->exporterFactory = new ExporterFactory();
    }

    public function saveToFile()
    {
        $this->filename = date('Ymd_His_').rand(0, 9999).'.jsonl';
        $this->file = fopen(
            _PS_MODULE_DIR_.'pluglin'.DIRECTORY_SEPARATOR.'files'.
            DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.$this->filename,
            'w'
        );

        while ($this->hasContent()) {
            $rows = $this->getContent(0);
            foreach ($rows as $row) {
                // we might need to change this, as it adds the unprocessed elements
                // to the list of "processed", but if the process fails we don't do
                // anything to set them as unprocessed.
                $this->processedIDs[] = $row['id_pluglin_content'];
                $data = $this->exporterFactory
                    ->getExporter($row['type'])
                    ->getData($row);

                if (empty($data)) {
                    continue;
                }

                $this->writeLine($data);
            }

            // Will set the 'sent' flag to true on the processedIDs
            $this->updateContentAsSent();
        }

        fclose($this->file);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    private function hasContent(): bool
    {
        $pendingItems = (int) \Db::getInstance()
            ->getValue('SELECT count(1) FROM '._DB_PREFIX_.'pluglin_content WHERE `send`=0');

        return $pendingItems > 1;
    }

    private function writeLine($data)
    {
        fwrite($this->file, json_encode($data));
        fwrite($this->file, "\n");
    }

    private function getContent($limit = 0)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'pluglin_content WHERE `send`=0 AND `read`=0';
        if (0 != $limit) {
            $sql .= ' LIMIT 0,'.$limit;
        }

        return \Db::getInstance()->executeS($sql);
    }

    private function updateContentAsSent()
    {
        if (empty($this->processedIDs)) {
            return;
        }

        $sql = 'UPDATE '._DB_PREFIX_.'pluglin_content SET `send` = 1 WHERE id_pluglin_content IN('.implode(', ', $this->processedIDs).')';
        if (\Db::getInstance()->execute($sql)) {
            $this->processedIDs = [];
        }
    }
}
