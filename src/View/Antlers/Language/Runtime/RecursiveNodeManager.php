<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\RecursiveNode;

class RecursiveNodeManager
{
    protected static $registry = [];
    protected static $dataRegistry = [];
    protected static $depthMapping = [];
    protected static $namedDepthMapping = [];

    public static function resetRecursiveNodeState()
    {
        self::$registry = [];
        self::$dataRegistry = [];
        self::$depthMapping = [];
        self::$namedDepthMapping = [];
    }

    public static function getNamedMappings()
    {
        return self::$namedDepthMapping;
    }

    public static function incrementDepth(AntlersNode $node)
    {
        $rootRef = $node->getRootRef();

        if (! array_key_exists($rootRef, self::$depthMapping)) {
            self::$depthMapping[$rootRef] = 0;
        }

        $namedDepthMapping = $node->content.'_depth';

        if (! array_key_exists($namedDepthMapping, self::$namedDepthMapping)) {
            self::$namedDepthMapping[$namedDepthMapping] = 0;
        }

        self::$namedDepthMapping[$namedDepthMapping] += 1;

        if (! $node instanceof RecursiveNode || ! $node->isNestedRecursive) {
            self::$depthMapping[$rootRef] += 1;
        }
    }

    public static function decrementDepth(AntlersNode $node)
    {
        $namedDepthMapping = $node->content.'_depth';

        self::$namedDepthMapping[$namedDepthMapping] -= 1;

        if (! $node instanceof RecursiveNode || ! $node->isNestedRecursive) {
            self::$depthMapping[$node->getRootRef()] -= 1;
        }
    }

    public static function getNodeDepth(AntlersNode $node)
    {
        $rootRef = $node->getRootRef();

        if (! array_key_exists($rootRef, self::$depthMapping)) {
            return 1;
        }

        if ($node instanceof RecursiveNode && $node->isNestedRecursive) {
            $namedDepthMapping = $node->content.'_depth';

            return self::$namedDepthMapping[$namedDepthMapping];
        }

        return self::$depthMapping[$rootRef];
    }

    public static function releaseRecursiveNode(AntlersNode $node)
    {
        $namedDepthMapping = $node->content.'_depth';

        if (array_key_exists($namedDepthMapping, self::$namedDepthMapping) && self::$namedDepthMapping[$namedDepthMapping] > 1) {
            self::decrementDepth($node);

            return;
        }

        unset(self::$depthMapping[$node->getRootRef()]);
        unset(self::$namedDepthMapping[$namedDepthMapping]);
    }

    public static function getActiveDepthNames()
    {
        return self::$namedDepthMapping;
    }

    public static function registerRecursiveNode(AntlersNode $node, $data)
    {
        if ($node->recursiveReference == null) {
            return;
        }

        self::$registry[$node->recursiveReference->name->name] = $node;
        self::$dataRegistry[$node->recursiveReference->name->name] = $data;
    }

    public static function getRecursiveNode(RecursiveNode $node)
    {
        if (array_key_exists($node->name->name, self::$registry)) {
            return self::$registry[$node->name->name];
        }

        return null;
    }

    public static function getRecursiveRootData(RecursiveNode $node)
    {
        if (array_key_exists($node->name->name, self::$dataRegistry)) {
            return self::$dataRegistry[$node->name->name];
        }

        return [];
    }
}
