<?php

namespace Pluglin\Prestashop\Services;

class Counter
{
    private $currentCount = 0;

    public function __construct()
    {
    }

    public function addWords($content): void
    {
        $this->currentCount += $this->countWordsFromString($content);
    }

    public function getCurrentCount(): int
    {
        return $this->currentCount;
    }

    private function countWordsFromString($string): int
    {
        $string = strip_tags(strtolower($string));
        $wRgx = '/[-\'\w{L}\xC2\xAD]+/u';

        if (false !== preg_match_all($wRgx, $string, $m)) {
            return count($m[0]);
        }

        $lastError = preg_last_error();
        $chkUtf8 = (PREG_BAD_UTF8_ERROR == $lastError);
        if ($chkUtf8) {
            return $this->countWordsFromString(iconv('CP1252', 'UTF-8', $string));
        }

        return 0;
    }
}
