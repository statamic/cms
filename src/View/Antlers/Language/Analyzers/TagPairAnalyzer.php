<?php

namespace Statamic\View\Antlers\Language\Analyzers;

use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ExecutionBranch;
use Statamic\View\Antlers\Language\Nodes\EscapedContentNode;
use Statamic\View\Antlers\Language\Nodes\RecursiveNode;
use Statamic\View\Antlers\Language\Nodes\TagIdentifier;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Runtime\NoParseManager;

class TagPairAnalyzer
{
    private $closingTagIndex = [];
    private $closingTagIndexCount = [];
    private $openTagIndexCount = [];
    private $closingTagNames = [];
    private $parentNode = null;

    private $stackCount = 0;

    /**
     * @var ExecutionBranch[]
     */
    private $createdExecutionBranches = [];

    /**
     * @var DocumentParser| null
     */
    private $document = null;

    private function getClosingCandidates($node)
    {
        if ($node instanceof AntlersNode && $node->isClosingTag == false) {
            if ($node->name->compound == 'if') {
                return ['if', 'elseif', 'else'];
            } elseif ($node->name->compound == 'elseif') {
                return ['elseif', 'else', 'if'];
            } elseif ($node->name->compound == 'else') {
                return ['if'];
            }

            return [
                $node->name->compound,
            ];
        }

        return [];
    }

    private function buildCloseIndex($nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof RecursiveNode) {
                continue;
            }

            if ($node instanceof AntlersNode && $node->isSelfClosing) {
                continue;
            }

            if ($node instanceof AntlersNode && $node->isClosingTag) {
                if (array_key_exists($this->stackCount, $this->closingTagIndex) == false) {
                    $this->closingTagIndex[$this->stackCount] = [];
                }

                if (array_key_exists($this->stackCount, $this->closingTagIndexCount) == false) {
                    $this->closingTagIndexCount[$this->stackCount] = [];
                }

                if (array_key_exists($node->name->compound, $this->closingTagIndex[$this->stackCount]) == false) {
                    $this->closingTagIndex[$this->stackCount][$node->name->compound] = [];
                    $this->closingTagIndexCount[$this->stackCount][$node->name->compound] = 0;
                }

                $this->closingTagIndex[$this->stackCount][$node->name->compound][] = $node;
                $this->closingTagIndexCount[$this->stackCount][$node->name->compound] += 1;
            }
        }

        if (array_key_exists($this->stackCount, $this->closingTagNames) == false) {
            $this->closingTagNames[$this->stackCount] = [];
        }

        // Process the closing tag index, if it has been set for the current stack level.
        if (array_key_exists($this->stackCount, $this->closingTagIndex)) {
            $this->closingTagNames[$this->stackCount] = array_keys($this->closingTagIndex[$this->stackCount]);

            foreach ($this->closingTagIndex[$this->stackCount] as $tagName => $indexedNodes) {
                $indexedNodeCount = count($indexedNodes);

                if ($indexedNodeCount == 0) {
                    continue;
                }

                // Find the last closing tag candidate, and work up
                // to calculate a list of valid opening candidates.
                /** @var AntlersNode $lastIndexedNode */
                $lastIndexedNode = $indexedNodes[$indexedNodeCount - 1];

                foreach ($nodes as $node) {
                    if ($node instanceof AntlersNode) {
                        if ($node instanceof RecursiveNode) {
                            continue;
                        }
                        if ($node->isComment) {
                            continue;
                        }
                        if ($node->index >= $lastIndexedNode->index) {
                            break;
                        }

                        if ($node->isClosingTag == false && $tagName == $node->name->compound && ! $node->isSelfClosing) {
                            if (array_key_exists($this->stackCount, $this->openTagIndexCount) == false) {
                                $this->openTagIndexCount[$this->stackCount] = [];
                            }

                            if (array_key_exists($tagName, $this->openTagIndexCount[$this->stackCount]) == false) {
                                $this->openTagIndexCount[$this->stackCount][$tagName] = 0;
                            }

                            $this->openTagIndexCount[$this->stackCount][$tagName] += 1;
                        }
                    }
                }
            }
        }
    }

    private function getScanForList($node)
    {
        if ($node instanceof AntlersNode) {
            if ($node->name->compound == 'else') {
                return ['if'];
            }

            if ($node->name->compound == 'elseif') {
                return ['elseif', 'else', 'if'];
            }

            if ($node->isClosingTag == false) {
                $candidates = $this->getClosingCandidates($node);

                return array_intersect($candidates, $this->closingTagNames[$this->stackCount]);
            }
        }

        return [];
    }

    private function canPossiblyClose($node)
    {
        if ($node instanceof RecursiveNode) {
            return false;
        }

        if ($node instanceof AntlersNode) {
            if ($node->isComment) {
                return false;
            }

            if ($node->isSelfClosing) {
                return false;
            }

            if ($node->name->compound == 'else' || $node->name->compound == 'elseif') {
                return true;
            }

            if ($node->isClosingTag == false) {
                $candidates = $this->getClosingCandidates($node);

                $overlap = array_intersect($candidates, $this->closingTagNames[$this->stackCount]);

                return ! empty($overlap);
            }
        }

        return false;
    }

    /**
     * @param  AbstractNode[]  $nodes
     * @param  AntlersNode  $node
     * @param $scanFor
     */
    private function findClosingPair($nodes, $node, $scanFor)
    {
        $scanForFlip = array_flip($scanFor);

        $refStack = 0;
        $refRuntimeNodeCount = count($node->runtimeNodes);

        foreach ($nodes as $candidateNode) {
            if ($candidateNode instanceof AntlersNode) {
                if ($node->endPosition->isBefore($candidateNode->startPosition)) {
                    if ($candidateNode->isClosingTag && $candidateNode->isOpenedBy != null) {
                        continue;
                    }
                    if ($candidateNode->isComment) {
                        continue;
                    }
                    if ($candidateNode->isSelfClosing) {
                        continue;
                    }

                    if ($candidateNode->isClosingTag == false) {
                        //if (in_array($candidateNode->name->compound, $scanFor)) {
                        if (array_key_exists($candidateNode->name->compound, $scanForFlip)) {
                            $refOpen = $this->openTagIndexCount[$this->stackCount][$candidateNode->name->compound];
                            $refClose = $this->closingTagIndexCount[$this->stackCount][$candidateNode->name->compound];

                            // Here we will check some details on the candidate node to
                            // determine what the "priority" of this node is.
                            if ($refOpen != $refClose) {
                                if (count($candidateNode->runtimeNodes) >= $refRuntimeNodeCount) {
                                    continue;
                                }
                            }

                            $refStack += 1;
                        }
                        continue;
                    }

                    if (array_key_exists($candidateNode->name->compound, $scanForFlip)) {
                        if ($refStack > 0) {
                            $refStack -= 1;
                            continue;
                        }
                    }

                    if ($refStack == 0 && array_key_exists($candidateNode->name->compound, $scanForFlip)) {
                        $candidateNode->isOpenedBy = $node;
                        $node->isClosedBy = $candidateNode;
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param $documentNodes
     * @param $document
     * @return array
     *
     * @throws SyntaxErrorException
     */
    public function associate($documentNodes, $document)
    {
        $this->document = $document;

        // Maintain our own stack to avoid recursive calls.
        // Each item on the stack will have this order:
        //     [$nodes, $parent|null]
        //
        // The document will remain the same across all.
        $nodeStack = [[$documentNodes, null]];
        $nodesToReturn = [];

        while (! empty($nodeStack)) {
            $details = array_pop($nodeStack);

            $nodes = $details[0];
            $this->parentNode = $details[1];

            $this->stackCount += 1;

            $this->buildCloseIndex($nodes);

            // Ask the specialized control structure analyzer to do it's job first.
            $nodes = ConditionPairAnalyzer::pairConditionals($nodes);

            foreach ($nodes as $node) {
                if ($node instanceof AntlersNode && $node->isComment) {
                    continue;
                }
                if ($node instanceof AntlersNode && $this->canPossiblyClose($node)) {
                    if (ConditionPairAnalyzer::isConditionalStructure($node)) {
                        continue;
                    }

                    $scanFor = $this->getScanForList($node);
                    $this->findClosingPair($nodes, $node, $scanFor);
                }
            }

            // Step 1: Set the node parent relationships.
            $nodeCount = count($nodes);
            for ($i = 0; $i < $nodeCount; $i++) {
                $node = $nodes[$i];

                if ($node instanceof AntlersNode && $node->isClosedBy != null) {
                    for ($j = $i + 1; $j < $nodeCount; $j++) {
                        $childNode = $nodes[$j];

                        if ($childNode->index > $node->isClosedBy->index) {
                            break;
                        }

                        $childNode->parent = $node;

                        $node->children[] = $childNode;

                        if ($childNode instanceof AntlersNode && $childNode->index == $node->index) {
                            $childNode->parent = $node;
                            break;
                        }
                    }
                }
            }

            // Step 2: Build up the inner children nodes.
            foreach ($nodes as $node) {
                if ($node instanceof AntlersNode && ! empty($node->children)) {
                    $newChildren = [];

                    foreach ($node->children as $childNode) {
                        if ($childNode->parent != null) {
                            if ($childNode->parent->index == $node->index) {
                                $childNode->parent = $node;
                                $newChildren[] = $childNode;
                            }
                        }
                    }

                    $nodeStack[] = [$newChildren, $node];
                }
            }

            // Step 3: Extract the "root" nodes. These will be our new "nested" nodes.
            $nestedNodes = [];

            foreach ($nodes as $node) {
                if ($this->parentNode == null) {
                    if ($node->parent == null) {
                        $nestedNodes[] = $node;
                    }
                } else {
                    if ($node->parent === $this->parentNode) {
                        $nestedNodes[] = $node;
                    }
                }
            }

            $nestedNodes = $this->reduceConditionals($nestedNodes);
            $nestedNodeKeyMap = [];

            foreach ($nodes as $node) {
                if ($node instanceof  AntlersNode && $node->isClosedBy != null) {
                    $content = $this->document->getText(
                        $node->endPosition->index + 1,
                        $node->isClosedBy->startPosition->index);

                    $node->runtimeContent = $content;
                }
            }

            for ($i = 0; $i < count($nestedNodes); $i++) {
                $node = $nestedNodes[$i];
                $nestedNodeKeyMap[$node->refId] = 1;

                if ($node instanceof AntlersNode &&
                    $node->isComment == false &&
                    $node->name->name == 'noparse' &&
                    ($node instanceof EscapedContentNode) == false &&
                    $node->isClosingTag == false) {
                    if ($node->isClosedBy == null) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_NO_PARSE_UNASSOCIATED,
                            $node,
                            'Encountered noparse region without a closing tag. All noparse regions must be closed.'
                        );
                    }

                    $content = $this->document->getText(
                        $node->endPosition->index + 1,
                        $node->isClosedBy->startPosition->index);

                    $noParseNode = new EscapedContentNode();
                    $parser = $node->getParser();
                    $noParseNode->withParser($parser);

                    $node->copyBasicDetailsTo($noParseNode);
                    $noParseNode->name = new TagIdentifier();
                    $noParseNode->name->name = 'noparse';
                    $noParseNode->name->compound = 'noparse';

                    $noParseNode->content = NoParseManager::registerNoParseContent($content);
                    $noParseNode->originalNode = $node;

                    $nestedNodes[$i] = $noParseNode;
                }
            }

            if ($this->parentNode != null && $this->parentNode instanceof AntlersNode) {
                $this->parentNode->children = $nestedNodes;

                if ($this->parentNode->parent != null && $this->parentNode->parent instanceof AntlersNode) {
                    $ancestorNodes = $this->parentNode->parent->children;
                    $newAncestorNodes = [];

                    foreach ($ancestorNodes as $aNode) {
                        // Because we are processing the deeply nested
                        // nodes *after* their parent nodes, we have
                        // to make sure to clean up the node tree.

                        if (! array_key_exists($aNode->refId, $nestedNodeKeyMap)) {
                            $newAncestorNodes[] = $aNode;
                        }
                    }

                    $this->parentNode->parent->children = $newAncestorNodes;
                }
            }

            if ($this->stackCount <= 1) {
                $nodesToReturn = $nestedNodes;
            }
        }

        if (! empty($this->createdExecutionBranches)) {
            foreach ($this->createdExecutionBranches as $branch) {
                // The execution branch's head node's children
                // may have changed due to how children
                // are processed non-recursively.
                $branch->nodes = $branch->head->children;
            }
        }

        return $nodesToReturn;
    }

    private function findEndOfBranch($nodes, $start, $startedAt)
    {
        $children = [];
        $tail = null;
        $offset = $startedAt;

        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if ($node instanceof AntlersNode && $node->isOpenedBy != null && $node->isOpenedBy == $start) {
                $tail = $node;
                break;
            } else {
                $children[] = $node;
            }
        }

        return [$tail, $children, $offset];
    }

    private function reduceConditionals($nodes)
    {
        $reduced = [];

        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if ($node instanceof AntlersNode && $node->isComment == false &&
                $node->name->compound == 'if' && $node->isClosingTag == false &&
                $node->isClosedBy != null) {
                $conditionNode = new ConditionNode();
                $conditionNode->index = $node->index;
                $conditionNode->chain[] = $node->index;

                if ($this->parentNode != null) {
                    $conditionNode->parent = $this->parentNode;
                }

                $exitedOn = null;

                while (true) {
                    $result = $this->findEndOfBranch(array_slice($nodes, $i + 1), $node, $i);

                    /** @var AntlersNode $tail */
                    $tail = $result[0];

                    $executionBranch = new ExecutionBranch();
                    $executionBranch->head = $node;
                    $executionBranch->tail = $tail;
                    $executionBranch->nodes = $node->children;

                    if ($tail == null) {
                        $tail = $executionBranch->head->isClosedBy;
                    }

                    $executionBranch->startPosition = $tail->startPosition;

                    $executionBranch->index = $tail->index;

                    if (! empty($executionBranch->nodes)) {
                        $executionBranch->endPosition = $executionBranch->nodes[count($executionBranch->nodes) - 1]->endPosition;
                    } else {
                        $executionBranch->endPosition = $tail->endPosition;
                    }

                    // Maintain a record of all created execution branches.
                    $this->createdExecutionBranches[] = $executionBranch;

                    $conditionNode->logicBranches[] = $executionBranch;

                    if ($tail->isClosingTag && $tail->name->compound == 'if') {
                        $exitedOn = $result[2];
                        break;
                    } else {
                        $conditionNode->chain[] = $tail->index;
                        $i = $result[2];
                        $node = $tail;
                    }
                }

                if (! empty($conditionNode->logicBranches)) {
                    $conditionNode->startPosition = $conditionNode->logicBranches[0]->startPosition;
                    $conditionNode->endPosition = $conditionNode->logicBranches[count($conditionNode->logicBranches) - 1]->endPosition;
                }

                $reduced[] = $conditionNode;

                if ($exitedOn != null) {
                    if ($exitedOn == count($nodes)) {
                        break;
                    }
                }
            } else {
                $reduced[] = $node;
            }
        }

        return $reduced;
    }
}
