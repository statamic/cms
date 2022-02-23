<?php

namespace Statamic\View\Antlers\Language\Runtime;

class LiteralReplacementManager
{
    protected static $regions = [];
    protected static $replacements = [];

    public static function registerRegion($name, $default)
    {
        $name = '__literalReplacement::_'.md5($name);
        self::$regions[$name] = $default;

        return $name;
    }

    public static function registerRegionReplacement($name, $string)
    {
        $name = '__literalReplacement::_'.md5($name);
        self::$replacements[$name] = $string;
    }

    public static function processReplacements($content)
    {
        if (empty(self::$regions)) {
            return $content;
        }

        foreach (self::$regions as $regionName => $defaultContent) {
            if (array_key_exists($regionName, self::$replacements)) {
                $replacement = (string) self::$replacements[$regionName];

                $content = str_replace($regionName, $replacement, $content);
            } else {
                $content = str_replace($regionName, $defaultContent, $content);
            }
        }

        return $content;
    }
}
