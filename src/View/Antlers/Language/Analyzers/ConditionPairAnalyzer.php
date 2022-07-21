<?php

namespace Statamic\View\Antlers\Language\Analyzers;

use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Utilities\NodeHelpers;

class ConditionPairAnalyzer
{
    /**
     * Tests if the provided node represents a conditional control structure.
     *
     * @param  AntlersNode  $node  The node to check.
     * @return bool
     */
    public static function isConditionalStructure(AntlersNode $node)
    {
        if ($node->isComment) {
            return false;
        }

        $name = $node->name->name;

        if ($name == 'if' || $name == 'elseif' || $name == 'else') {
            return true;
        }

        return false;
    }

    /**
     * Tests if the provided node still requires a closing pair.
     *
     * @param  AntlersNode  $node  The node to check.
     * @return bool
     */
    protected static function requiresClose(AntlersNode $node)
    {
        $name = $node->name->name;

        if ($name == 'elseif' || $name == 'else') {
            if ($node->isClosedBy != null) {
                return false;
            }

            return true;
        }

        if ($node->isClosingTag) {
            return false;
        }

        if ($node->isClosedBy != null) {
            return false;
        }

        return true;
    }

    /**
     * A list of valid closing names for common conditional node types.
     *
     * We are returning an array of integers so we can use array_key_exists later.
     *
     * @var int[]
     */
    protected static $conditionClosingPairs = ['elseif' => 1, 'else' => 1];

    /**
     * Returns a list of valid closing node names for the provided node.
     *
     * @param  string  $current  The current node's name.
     * @return array|int[]
     */
    protected static function getValidClosingPairs($current)
    {
        if ($current == 'if' || $current == 'elseif') {
            return self::$conditionClosingPairs;
        }

        return [];
    }

    /**
     * Descends through the nodes to find the closest logical
     * closing node for each opening conditional node type.
     *
     * @param  AbstractNode[]  $nodes  The nodes to analyze.
     * @param  AntlersNode  $node  The primary node.
     * @param  int  $index  The primary node starting index.
     *
     * @throws SyntaxErrorException
     */
    protected static function findClosestStructurePair($nodes, $node, $index)
    {
        $stack = [[$node, $index]];
        $nodeLen = count($nodes);

        while (! empty($stack)) {
            $curItem = array_pop($stack);
            $curNode = $curItem[0];
            $curIndex = $curItem[1];
            $thisValidPairs = self::getValidClosingPairs($curNode->name->name);

            $doSkipValidation = false;

            for ($i = $curIndex; $i < $nodeLen; $i++) {
                $subNode = $nodes[$i];

                if ($subNode instanceof AntlersNode) {
                    if (self::isConditionalStructure($subNode)) {
                        if (self::requiresClose($subNode)) {
                            $stack[] = $curItem; // Replace the current item since we are not done yet.
                            $stack[] = [$subNode, $i + 1]; // Place this one on since we need to look for it next.
                            $doSkipValidation = true;
                            break;
                        }

                        if ($curNode->isClosedBy != null) {
                            continue;
                        }

                        $canClose = false;

                        if ($subNode->ref == 0 && (($subNode->isClosingTag && $subNode->name->name == 'if') ||
                                array_key_exists($subNode->name->name, $thisValidPairs)
                            )) {
                            $canClose = true;
                        }

                        if ($subNode->refId == $curNode->refId) {
                            $canClose = false;
                        }

                        if ($canClose) {
                            $curNode->isClosedBy = $subNode;
                            $subNode->isOpenedBy = $curNode;
                            $subNode->ref += 1;
                            $doSkipValidation = true;
                            break;
                        }
                    }
                }
            }

            if (! $doSkipValidation) {
                if ($curNode instanceof AntlersNode) {
                    if (($curNode->name->name == 'elseif' || $curNode->name->name == 'else') &&
                        $curNode->isOpenedBy == null) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_PARSE_UNPAIRED_CONDITIONAL,
                            $curNode,
                            'Unpaired "'.NodeHelpers::getTrueName($curNode).'" control structure.'
                        );
                    }

                    if ($curNode->isClosedBy == null && self::requiresClose($curNode)) {
                        $errorMessage = 'Unclosed "'.NodeHelpers::getTrueName($curNode).'" control structure.';

                        if ($curNode->isInterpolationNode) {
                            $errorMessage .= ' Tag pairs are not supported within Antlers tags.';
                        }

                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_PARSE_UNCLOSED_CONDITIONAL,
                            $curNode,
                            $errorMessage
                        );
                    }
                }
            }
        }
    }

    /**
     * Checks each node, and pairs any un-paired conditional nodes.
     *
     * @param  AbstractNode[]  $nodes  The nodes to pair.
     * @return AbstractNode[]
     *
     * @throws SyntaxErrorException
     */
    public static function pairConditionals($nodes)
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if ($node instanceof AntlersNode && self::isConditionalStructure($node)) {
                if (self::requiresClose($node)) {
                    self::findClosestStructurePair($nodes, $node, $i + 1);
                }
            }
        }

        foreach ($nodes as $node) {
            if ($node instanceof AntlersNode && self::isConditionalStructure($node)) {
                if (($node->name->name == 'elseif' || $node->name->name == 'else') &&
                    $node->isOpenedBy == null) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_PARSE_UNPAIRED_CONDITIONAL,
                        $node,
                        'Unpaired "'.NodeHelpers::getTrueName($node).'" control structure.'
                    );
                }

                if ($node->isClosedBy == null && self::requiresClose($node)) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_PARSE_UNCLOSED_CONDITIONAL,
                        $node,
                        'Unclosed '.NodeHelpers::getTrueName($node).' control structure.'
                    );
                }
            }
        }

        return $nodes;
    }
}
