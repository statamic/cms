<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Statamic\Support\Str;

class LiteralReplacementManager
{
    protected static $regions = [];
    protected static $replacements = [];
    protected static $globalReplacement = [];
    protected static $registeredSections = [];
    protected static $retargeted = [];

    public static function resetLiteralState()
    {
        self::$regions = [];
        self::$replacements = [];
        self::$globalReplacement = [];
        self::$retargeted = [];
        self::$registeredSections = [];
    }

    public static function registerRegion($name, $section, $default)
    {
        $name = '__literalReplacement::_'.md5($name);
        $globalName = 'section:'.$section.'__yield';
        $globalReplacement = '__literalReplacement::_'.md5($globalName);

        self::$globalReplacement[$name] = $globalReplacement;

        if (array_key_exists($globalReplacement, self::$regions)) {
            if (! array_key_exists($globalReplacement, self::$retargeted)) {
                self::$retargeted[$globalReplacement] = [];
            }

            self::$retargeted[$globalReplacement][] = $name;

            return $name;
        }

        self::$regions[$name] = $default ?? '';

        return $name;
    }

    /**
     * Tests if a section name bas been registered with the manager.
     *
     * @param  string  $name  The section name.
     * @return bool
     */
    public static function hasRegisteredSectionName($name)
    {
        return in_array($name, self::$registeredSections);
    }

    public static function registerRegionReplacement($name, $tagMethod, $string)
    {
        // Keep a record of all the section names we've registered.
        if (! in_array($tagMethod, self::$registeredSections)) {
            self::$registeredSections[] = $tagMethod;
        }

        $name = '__literalReplacement::_'.md5($name);

        $string = (string) $string;

        if (Str::contains($string, $name)) {
            $swap = self::$globalReplacement[$name];
            $string = str_replace($name, $swap, $string);
            unset(self::$regions[$name]);
            unset(self::$replacements[$name]);

            if (array_key_exists($swap, self::$regions)) {
                $swapContent = self::$regions[$swap];
                $string = str_replace($swap, $swapContent, $string);
                self::$regions[$swap] = $string;
                self::$replacements[$swap] = $string;

                return;
            }

            self::$regions[$swap] = $string;
            self::$replacements[$swap] = $string;

            return;
        }

        if (array_key_exists($name, self::$replacements)) {
            $existing = (string) self::$replacements[$name];
            $incoming = (string) $string;

            if (Str::contains($existing, $name) && Str::contains($incoming, $name)) {
                $incoming = str_replace($name, $existing, $incoming);

                self::$replacements[$name] = $incoming;
            } else {
                self::$replacements[$name] = $string;
            }
        } else {
            self::$replacements[$name] = $string;
        }
    }

    protected static function replaceAllNames($content)
    {
        $names = array_keys(self::$regions);

        foreach ($names as $name) {
            $content = str_replace($name, '', $content);
        }

        return $content;
    }

    public static function processReplacements($content)
    {
        if (empty(self::$regions)) {
            return self::replaceAllNames($content);
        }

        foreach (self::$regions as $regionName => $defaultContent) {
            if (array_key_exists($regionName, self::$replacements)) {
                $replacement = (string) self::$replacements[$regionName];

                $content = str_replace($regionName, $replacement, $content);
            } else {
                $content = str_replace($regionName, $defaultContent, $content);
            }
        }

        foreach (self::$retargeted as $globalName => $adjusted) {
            foreach ($adjusted as $replaced) {
                if (Str::contains($content, $replaced)) {
                    $replaceContent = self::$regions[$globalName];
                    $content = str_replace($replaced, $replaceContent, $content);
                }
            }
        }

        return self::replaceAllNames($content);
    }
}
