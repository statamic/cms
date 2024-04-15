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
    protected static $recursionStack = [];

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

    public static function updateNamedDepth(AntlersNode $node, $depth)
    {
        $namedDepthMapping = $node->content.'_depth';

        if (! array_key_exists($namedDepthMapping, self::$namedDepthMapping)) {
            self::$namedDepthMapping[$namedDepthMapping] = 0;
        }

        self::$namedDepthMapping[$namedDepthMapping] = $depth;
    }

    public static function decrementDepth(AntlersNode $node)
    {
        $namedDepthMapping = $node->content.'_depth';

        self::$namedDepthMapping[$namedDepthMapping] -= 1;

        if (! $node instanceof RecursiveNode || ! $node->isNestedRecursive) {
            $rootRefId = $node->getRootRef();

            if (array_key_exists($rootRefId, self::$depthMapping)) {
                self::$depthMapping[$rootRefId] -= 1;
            }
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

        if ($node instanceof RecursiveNode && $node->recursiveParent != null) {
            $refId = $node->recursiveParent->getRootRef();

            if (array_key_exists($refId, self::$recursionStack)) {
                // Reduce the depth of the parent node.
                self::$recursionStack[$refId] -= 1;

                if (self::$recursionStack[$refId] < 0) {
                    // At this point we've released the ultimate parent and are moving on.
                    // We will do a hard reset instead of the normal depth decrement.
                    unset(self::$recursionStack[$refId]);
                    unset(self::$depthMapping[$node->getRootRef()]);
                    unset(self::$namedDepthMapping[$namedDepthMapping]);

                    return;
                }
            }

            GlobalRuntimeState::$activeTracerCount -= 1;
        }

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

        // Get the reference id for the ultimate recursive parent.
        $refId = $node->recursiveReference->recursiveParent->getRootRef();

        if (! array_key_exists($refId, self::$recursionStack)) {
            self::$recursionStack[$refId] = 0;
        }

        // Keep track of how nested we are inside the recursive node.
        self::$recursionStack[$refId] += 1;

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
