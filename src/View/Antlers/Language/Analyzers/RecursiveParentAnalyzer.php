<?php

namespace Statamic\View\Antlers\Language\Analyzers;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\RecursiveNode;

class RecursiveParentAnalyzer
{
    /**
     * Locates recursive node parents.
     *
     * @param  AbstractNode[]  $nodes  The nodes to associate.
     *
     * @throws SyntaxErrorException
     */
    public static function associateRecursiveParent($nodes)
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if ($node instanceof RecursiveNode) {
                $recursiveContent = '*recursive '.$node->name->name.'*';

                if ($i - 1 < 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_RECURSIVE_NODE_INVALID_POSITION,
                        $node,
                        'Unpaired recursive node. All recursive nodes must have a parent node introducing them.'
                    );
                }

                $lastNode = null;
                for ($j = $i - 1; $j >= 0; $j -= 1) {
                    $subNode = $nodes[$j];

                    if ($subNode instanceof LiteralNode) {
                        continue;
                    }

                    if ($subNode instanceof AntlersNode && $subNode->isClosedBy != null) {
                        if ($node->isNestedRecursive) {
                            if (trim($subNode->content) == $node->name->name) {
                                $lastNode = $subNode;
                                break;
                            }
                        } else {
                            if (Str::contains($subNode->runtimeContent, $recursiveContent) && mb_substr_count($subNode->runtimeContent, '*recursive') == 1 && $node->getRootRef() == $subNode->getRootRef()) {
                                $lastNode = $subNode;

                                // Ensure we stop searching once we reach the closest nav parent.
                                if ($subNode->name->name == 'nav') {
                                    break;
                                }

                                continue;
                            } else {
                                if ($lastNode != null) {
                                    break;
                                }
                            }
                        }
                    }
                }

                $lastNode = self::findNextNonConditional($lastNode);

                if ($lastNode == null) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_RECURSIVE_UNPAIRED_NODE,
                        $node,
                        'Unpaired recursive node. All recursive nodes must have a parent node introducing them.'
                    );
                }

                if ($node->isNestedRecursive) {
                    $lastNode->hasRecursiveNode = true;
                    $node->recursiveParent = $lastNode;
                    $lastNode->recursiveReference = $node;
                } else {
                    if ($lastNode->parent != null) {
                        $lastNode->parent->hasRecursiveNode = true;
                        $lastNode->parent->recursiveReference = $node;
                        $node->recursiveParent = $lastNode->parent;
                    }

                    $lastNode->hasRecursiveNode = true;
                    $node->recursiveParent = $lastNode;
                    $lastNode->recursiveReference = $node;
                }
            }
        }
    }

    private static function findNextNonConditional(AntlersNode $node)
    {
        if (ConditionPairAnalyzer::isConditionalStructure($node) == false) {
            return $node;
        }

        if ($node->parent == null) {
            return $node;
        }

        return self::findNextNonConditional($node->parent);
    }
}
