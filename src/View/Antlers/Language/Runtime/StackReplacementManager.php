<?php

namespace Statamic\View\Antlers\Language\Runtime;

class StackReplacementManager
{
    protected static $stacks = [];
    protected static $stackContents = [];

    public static function clearStackState()
    {
        self::$stackContents = [];
        self::$stacks = [];
    }

    public static function registerStack($name)
    {
        $name = '__stackReplacement::_'.md5($name);

        self::$stacks[$name] = 1;

        return $name;
    }

    public static function prependStack($stackName, $content)
    {
        $name = '__stackReplacement::_'.md5($stackName);

        if (! array_key_exists(GlobalRuntimeState::$environmentId, self::$stackContents)) {
            self::$stackContents[GlobalRuntimeState::$environmentId] = [];
        }

        if (! array_key_exists($name, self::$stackContents[GlobalRuntimeState::$environmentId])) {
            self::$stackContents[GlobalRuntimeState::$environmentId][$name] = [];
        }

        array_unshift(self::$stackContents[GlobalRuntimeState::$environmentId][$name], $content);
    }

    public static function pushStack($stackName, $content)
    {
        $name = '__stackReplacement::_'.md5($stackName);

        if (! array_key_exists(GlobalRuntimeState::$environmentId, self::$stackContents)) {
            self::$stackContents[GlobalRuntimeState::$environmentId] = [];
        }

        if (! array_key_exists($name, self::$stackContents[GlobalRuntimeState::$environmentId])) {
            self::$stackContents[GlobalRuntimeState::$environmentId][$name] = [];
        }

        self::$stackContents[GlobalRuntimeState::$environmentId][$name][] = $content;
    }

    public static function processReplacements($content)
    {
        if (empty(self::$stacks)) {
            return $content;
        }

        foreach (self::$stacks as $stackName => $throwAway) {
            $replacement = '';

            if (array_key_exists($stackName, self::$stackContents[GlobalRuntimeState::$environmentId])) {
                $stackedContent = self::$stackContents[GlobalRuntimeState::$environmentId][$stackName];

                foreach ($stackedContent as $stackContent) {
                    $replacement .= trim($stackContent);
                }
            }

            $content = str_replace($stackName, $replacement, $content);
        }

        return $content;
    }
}
