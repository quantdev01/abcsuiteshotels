<?php

namespace Pluglin\Prestashop\Architecture;

use Exception;

class Singleton
{
    private static $instances = [];

    protected function __construct()
    {
    }

    /** @throws Exception */
    public function __wakeup()
    {
        throw new Exception('Cannot un-serialize singleton');
    }

    public static function getInstance()
    {
        $subclass = static::class;
        if (!isset(self::$instances[$subclass])) {
            self::$instances[$subclass] = new static();
        }

        return self::$instances[$subclass];
    }
}
