<?php

namespace Statamic\View\Antlers\Language\Utilities;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\NumberNode;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\VariableNode;

class NodeHelpers
{
    public static function getSimpleVarName(VariableNode $variableNode)
    {
        if ($variableNode->variableReference != null && count($variableNode->variableReference->pathParts) == 1) {
            return $variableNode->variableReference->pathParts[0]->name;
        }

        return '';
    }

    public static function isVariableMatching($node, $path)
    {
        if ($node instanceof VariableNode) {
            return $node->name == $path;
        }

        return false;
    }

    public static function getTrueName(AntlersNode $node)
    {
        if ($node->originalNode != null) {
            return $node->originalNode->name->name;
        }

        return $node->name->name;
    }

    public static function getSimpleNodeValue(AbstractNode $node)
    {
        if ($node instanceof NumberNode) {
            return $node->value;
        } elseif ($node instanceof StringValueNode) {
            return $node->value;
        } elseif ($node instanceof FalseConstant) {
            return false;
        } elseif ($node instanceof TrueConstant) {
            return true;
        }

        return null;
    }

    public static function mergeVarRight(VariableNode $left, VariableNode $right)
    {
        $right->startPosition = $left->startPosition;
        $right->name = $left->name.$right->name;

        return $right;
    }

    public static function mergeVarContentRight($content, AbstractNode $referenceNode, VariableNode $target)
    {
        $target->startPosition = $referenceNode->startPosition;
        $target->name = $content.$target->name;

        return $target;
    }

    public static function mergeVarContentLeft($content, AbstractNode $referenceNode, VariableNode $target)
    {
        $target->endPosition = $referenceNode->endPosition;
        $target->name = $target->name.$content;

        return $target;
    }

    public static function distance(AbstractNode $left, AbstractNode $right)
    {
        if ($left->endPosition == null || $right->startPosition == null) {
            return null;
        }

        return $right->startPosition->index - $left->endPosition->index;
    }

    public static function getContent(AntlersNode $node)
    {
        $curSetting = StringUtilities::$splitMethod;
        $content = trim($node->content);
        StringUtilities::prepareSplit($content);

        $val = StringUtilities::substr($content, mb_strlen($node->name->compound));
        StringUtilities::$splitMethod = $curSetting;

        return $val;
    }
}
