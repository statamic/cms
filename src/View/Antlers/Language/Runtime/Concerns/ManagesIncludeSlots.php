<?php

namespace Statamic\View\Antlers\Language\Runtime\Concerns;

use RuntimeException;
use Statamic\Tags\IncludeTag;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Slot;

trait ManagesIncludeSlots
{
    protected function captureIncludeSlots(AntlersNode $node, array $tagActiveData, array $tagParameters): array
    {
        foreach ($this->buildIncludeSlots($node, $tagActiveData) as $name => $slot) {
            $tagParameters[IncludeTag::SLOT_PARAM_PREFIX.$name] = $slot;
        }

        return $tagParameters;
    }

    protected function buildIncludeSlots(AntlersNode $node, array $callerData): array
    {
        $namedSlots = [];
        $defaultChildren = [];

        foreach ($node->children as $child) {
            if ($child instanceof AntlersNode && $child->isClosingTag) {
                continue;
            }

            if ($this->isNamedSlotNode($child)) {
                if (array_key_exists($name = $child->name->methodPart, $namedSlots)) {
                    throw new RuntimeException("The include tag cannot define the [{$name}] slot more than once.");
                }

                $namedSlots[$name] = $child;

                continue;
            }

            $defaultChildren[] = $child;
        }

        $slots = [];

        if ($this->slotHasContent($defaultChildren)) {
            $slots['slot'] = Slot::forAntlers($defaultChildren, $this->defaultSlotSource($node, $namedSlots), $callerData, $this);
        }

        foreach ($namedSlots as $slotName => $slotNode) {
            if ($this->slotHasContent($slotNode->children)) {
                $slots[$slotName] = Slot::forAntlers($slotNode->children, $slotNode->runtimeContent, $callerData, $this);
            }
        }

        return $slots;
    }

    protected function defaultSlotSource(AntlersNode $node, array $namedSlots): string
    {
        if (empty($namedSlots)) {
            return $node->runtimeContent;
        }

        $parser = $node->getParser();
        $start = $node->endPosition->index + 1;
        $source = '';

        foreach ($namedSlots as $slotNode) {
            $source .= $parser->getText($start, $slotNode->startPosition->index);
            $start = ($slotNode->isClosedBy ?? $slotNode)->endPosition->index + 1;
        }

        return $source.$parser->getText($start, $node->isClosedBy->startPosition->index);
    }

    protected function isNamedSlotNode($node): bool
    {
        return $node instanceof AntlersNode && ! $node->isComment &&
            $node->name != null && $node->name->name == 'slot' &&
            $node->name->methodPart != null;
    }

    protected function slotHasContent(array $children): bool
    {
        foreach ($children as $child) {
            if ($child instanceof LiteralNode) {
                if (trim($child->content) !== '') {
                    return true;
                }

                continue;
            }

            if ($child instanceof AntlersNode && ($child->isComment || $child->isClosingTag)) {
                continue;
            }

            return true;
        }

        return false;
    }

    protected function getSlotOutputProps(AntlersNode $node): array
    {
        $lockData = $this->data;
        $props = $node->getParameterValues($this, $this->getActiveData());
        $this->data = $lockData;

        return $props;
    }
}
