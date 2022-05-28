<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Position;

class StackReplacementManager
{
    protected static $stacks = [];
    protected static $arrayStacks = [];
    protected static $stackContents = [];

    public static function clearStackState()
    {
        self::$stackContents = [];
        self::$arrayStacks = [];
        self::$stacks = [];
    }

    protected static function getStackReplacement($name)
    {
        return '__stackReplacement::_'.md5($name);
    }

    public static function registerStack($name)
    {
        $name = self::getStackReplacement($name);

        self::$stacks[$name] = 1;

        return $name;
    }

    /**
     * @param  string  $name
     * @param  AntlersNode  $node
     * @param  Position  $processor
     * @return string
     */
    public static function registerArrayStack($name, $node, $processor)
    {
        $name = self::registerStack($name);

        if (! array_key_exists($name, self::$arrayStacks)) {
            self::$arrayStacks[$name] = [];
        }

        $contentHash = '_'.md5($node->documentText());

        self::$arrayStacks[$name][] = [$node, $processor, $contentHash];

        return $name.$contentHash;
    }

    public static function prependStack($stackName, $content, $trimContentWhitespace = true)
    {
        $name = self::getStackReplacement($stackName);

        if (! array_key_exists(GlobalRuntimeState::$environmentId, self::$stackContents)) {
            self::$stackContents[GlobalRuntimeState::$environmentId] = [];
        }

        if (! array_key_exists($name, self::$stackContents[GlobalRuntimeState::$environmentId])) {
            self::$stackContents[GlobalRuntimeState::$environmentId][$name] = [];
        }

        if ($trimContentWhitespace) {
            $content = trim($content);
        }

        array_unshift(self::$stackContents[GlobalRuntimeState::$environmentId][$name], $content);
    }

    public static function pushStack($stackName, $content, $trimContentWhitespace = true)
    {
        $name = self::getStackReplacement($stackName);

        if (! array_key_exists(GlobalRuntimeState::$environmentId, self::$stackContents)) {
            self::$stackContents[GlobalRuntimeState::$environmentId] = [];
        }

        if (! array_key_exists($name, self::$stackContents[GlobalRuntimeState::$environmentId])) {
            self::$stackContents[GlobalRuntimeState::$environmentId][$name] = [];
        }

        if ($trimContentWhitespace) {
            $content = trim($content);
        }

        self::$stackContents[GlobalRuntimeState::$environmentId][$name][] = $content;
    }

    public static function processReplacements($content)
    {
        if (empty(self::$stacks)) {
            return $content;
        }

        if (! array_key_exists(GlobalRuntimeState::$environmentId, self::$stackContents)) {
            foreach (self::$arrayStacks as $stackName => $stacks) {
                foreach ($stacks as $stack) {
                    $content = str_replace($stackName.$stack[2], '', $content);
                }
            }

            foreach (array_keys(self::$stacks) as $replacementString) {
                $content = str_replace($replacementString, '', $content);
            }

            return $content;
        }

        foreach (self::$stacks as $stackName => $throwAway) {
            $replacement = '';

            if (array_key_exists($stackName, self::$arrayStacks)) {
                $arrayStackDetails = self::$arrayStacks[$stackName];

                $stackItems = [];

                if (array_key_exists($stackName, self::$stackContents[GlobalRuntimeState::$environmentId])) {
                    $stackItems = self::$stackContents[GlobalRuntimeState::$environmentId][$stackName];
                }

                foreach ($arrayStackDetails as $arrayDetails) {
                    /** @var AntlersNode $node */
                    $node = $arrayDetails[0];

                    /** @var NodeProcessor $processor */
                    $processor = $arrayDetails[1];
                    $data = $processor->getActiveData();
                    $data['stack'] = [
                        $node->name->methodPart => $stackItems,
                    ];

                    $result = (string) $processor->setData($data)->reduce([$node]);

                    $content = str_replace($stackName.$arrayDetails[2], $result, $content);
                }
            } elseif (array_key_exists($stackName, self::$stackContents[GlobalRuntimeState::$environmentId])) {
                $stackedContent = self::$stackContents[GlobalRuntimeState::$environmentId][$stackName];

                foreach ($stackedContent as $stackContent) {
                    $replacement .= $stackContent;
                }
            }

            $content = str_replace($stackName, $replacement, $content);
        }

        return $content;
    }
}
