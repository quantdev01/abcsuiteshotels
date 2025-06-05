<?php

namespace Pluglin\Prestashop\Services;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pluglin\Prestashop\Architecture\Singleton;

class PluglinLogger extends Singleton
{
    protected $logger;

    protected function __construct()
    {
        parent::__construct();

        $this->logger = new Logger('develop');
        $this->logger->pushHandler(new RotatingFileHandler(_PS_MODULE_DIR_.'pluglin/log/develop.log', 0));
    }

    public function writeLog(string $message, $context = [], int $level = Logger::DEBUG): void
    {
        $this->logger->log($level, $message, $context);
    }

    public static function debug(string $message, $context = []): void
    {
        static::getInstance()->writeLog($message, $context);
    }

    public static function error(string $message, $context = []): void
    {
        static::getInstance()->writeLog($message, $context, Logger::ERROR);
    }
}
