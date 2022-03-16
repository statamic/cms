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

    public static function getNamedMappings()
    {
        return self::$namedDepthMapping;
    }

    public static function incrementDepth(AntlersNode $node)
    {
        if (! array_key_exists($node->refId, self::$depthMapping)) {
            self::$depthMapping[$node->refId] = 0;
        }

        $namedDepthMapping = $node->content.'_depth';

        if (! array_key_exists($namedDepthMapping, self::$namedDepthMapping)) {
            self::$namedDepthMapping[$namedDepthMapping] = 0;
        }

        self::$depthMapping[$node->refId] += 1;
        self::$namedDepthMapping[$namedDepthMapping] += 1;
    }

    public static function decrementDepth(AntlersNode $node)
    {
        $namedDepthMapping = $node->content.'_depth';

        self::$depthMapping[$node->refId] -= 1;
        self::$namedDepthMapping[$namedDepthMapping] -= 1;
    }

    public static function getNodeDepth(AntlersNode $node)
    {
        if (! array_key_exists($node->refId, self::$depthMapping)) {
            return 1;
        }

        return self::$depthMapping[$node->refId];
    }

    public static function releaseRecursiveNode(AntlersNode $node)
    {
        $namedDepthMapping = $node->content.'_depth';

        unset(self::$depthMapping[$node->refId]);
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
