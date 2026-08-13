<?php

namespace Statamic\View\Antlers\Language\Runtime\Concerns;

use Statamic\Tags\IncludeTag;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
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
                $namedSlots[$child->name->methodPart] = $child;

                continue;
            }

            $defaultChildren[] = $child;
        }

        $slots = [];

        if ($this->slotHasContent($defaultChildren)) {
            $slots['slot'] = $this->makeSlot($defaultChildren, $callerData);
        }

        foreach ($namedSlots as $slotName => $slotNode) {
            if ($this->slotHasContent($slotNode->children)) {
                $slots[$slotName] = $this->makeSlot($slotNode->children, $callerData);
            }
        }

        return $slots;
    }

    protected function makeSlot(array $nodes, array $callerData): Slot
    {
        $callerState = [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState];

        $renderer = function (array $data) use ($nodes, $callerState) {
            $tagState = [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState];

            [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState] = $callerState;

            try {
                return $this->cloneProcessor()->setData($data)->reduce($nodes);
            } finally {
                [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState] = $tagState;
            }
        };

        return new Slot($renderer, $callerData);
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
