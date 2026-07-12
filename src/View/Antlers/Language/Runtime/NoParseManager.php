<?php

namespace Statamic\View\Antlers\Language\Runtime;

class NoParseManager
{
    protected static $regions = [];

    public static function regions()
    {
        return self::$regions;
    }

    public static function clear()
    {
        self::$regions = [];
    }

    public static function registerNoParseContent($content)
    {
        $hash = md5($content);

        self::$regions[$hash] = $content;

        return $hash;
    }
}
