<?php

namespace Statamic\View\Antlers\Language\Analyzers;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;

class NodeTypeAnalyzer
{
    /**
     * @var EnvironmentDetails|null
     */
    public static $environmentDetails = null;

    public static function analyze($nodes, EnvironmentDetails $environmentDetails)
    {
        foreach ($nodes as $node) {
            if ($node instanceof AntlersNode) {
                if ($node->pathReference != null && $node->pathReference->isStrictVariableReference) {
                    $node->isTagNode = false;
                    continue;
                }

                $node->isTagNode = $environmentDetails->isTag($node->name->name);
            }
        }
    }

    public static function analyzeParametersForModifiers(AntlersNode $node)
    {
        foreach ($node->parameters as $parameter) {
            $parameter->isModifierParameter = self::$environmentDetails->isModifier($parameter->name);
        }
    }

    public static function analyzeNode(AntlersNode $node)
    {
        if ($node->pathReference != null && $node->pathReference->isStrictVariableReference) {
            $node->isTagNode = false;

            return;
        }

        $node->isTagNode = self::$environmentDetails->isTag($node->name->name);
    }
}
