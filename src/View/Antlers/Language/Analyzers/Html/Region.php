<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\LazyCollection;
use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;

/** @internal */
class Region
{
    protected AntlersNode $opening;

    protected AntlersNode $closing;

    public function __construct(AntlersNode $opening, AntlersNode $closing)
    {
        $this->opening = $opening;
        $this->closing = $closing;
    }

    public function opening(): AntlersNode
    {
        return $this->opening;
    }

    public function closing(): AntlersNode
    {
        return $this->closing;
    }

    public function document(): Document
    {
        return $this->opening->document();
    }

    public function name(): ?string
    {
        $name = $this->opening->antlersNode()->name;

        if ($name === null) {
            return null;
        }

        return $name->compound;
    }

    public function content(Node|string|null $content = null): NodeSequence|static
    {
        $this->assertOrderedSiblings();

        if (func_num_args() !== 0) {
            if ($content === $this->opening || $content === $this->closing) {
                throw new \LogicException('HTML region boundaries cannot become region content.');
            }

            $existing = NodeSequence::between($this->opening, $this->closing)
                ->nodes()
                ->all();

            $this->closing->before($content ?? '');

            foreach ($existing as $node) {
                if ($node !== $content) {
                    $node->remove();
                }
            }

            return $this;
        }

        return NodeSequence::between($this->opening, $this->closing);
    }

    /**
     * @param  callable(string, string, Text): (string|null)  $rewrite
     * @param  (callable(Element): bool)|null  $descend
     */
    public function rewriteTextEdges(callable $rewrite, ?callable $descend = null): static
    {
        $this->content()->rewriteTextEdges($rewrite, $descend);

        return $this;
    }

    /** @return LazyCollection<int, Element> */
    public function ownedElements(): LazyCollection
    {
        return LazyCollection::make(fn () => $this->ownedElementsWithin($this->content()))->values();
    }

    public function unwrap(): static
    {
        $this->assertOrderedSiblings();
        $this->closing->remove();
        $this->opening->remove();

        return $this;
    }

    public function wrapWith(Element $element): static
    {
        if ($element->document() !== $this->document()) {
            throw new \InvalidArgumentException('HTML graph nodes cannot be moved between documents.');
        }

        $this->assertOrderedSiblings();

        $members = [
            $this->opening,
            ...$this->content()->nodes()->all(),
            $this->closing,
        ];

        if (in_array($element, $members, true)) {
            throw new \LogicException('An HTML region cannot be wrapped with one of its own members.');
        }

        $this->opening->before($element);

        foreach ($members as $member) {
            $element->append($member);
        }

        return $this;
    }

    protected function ownedElementsWithin(NodeSequence $sequence): \Generator
    {
        $nestedClosing = null;

        foreach ($sequence->nodes() as $node) {
            if ($nestedClosing !== null) {
                if ($node === $nestedClosing) {
                    $nestedClosing = null;
                }

                continue;
            }

            if ($node instanceof AntlersNode) {
                $candidate = $node->antlersNode();

                if ($this->isNestedRegionOpening($candidate)) {
                    $closing = $candidate->isClosedBy->htmlNode();

                    if ($closing instanceof AntlersNode
                        && $closing->parent() === $node->parent()) {
                        $nestedClosing = $closing;

                        continue;
                    }
                }
            }

            if (! $node instanceof Element) {
                continue;
            }

            yield $node;
            yield from $this->ownedElementsWithin($node->content());
        }
    }

    protected function isNestedRegionOpening(LanguageAntlersNode $node): bool
    {
        if ($node->isClosingTag) {
            return false;
        }

        if (! $node->isClosedBy instanceof LanguageAntlersNode) {
            return false;
        }

        if ($node->name === null) {
            return false;
        }

        return strcasecmp($node->name->compound, (string) $this->name()) === 0;
    }

    protected function assertOrderedSiblings(): void
    {
        $parent = $this->opening->parent();

        if ($parent === null || $parent !== $this->closing->parent()) {
            throw new \LogicException('HTML regions must begin and end as children of the same container.');
        }

        if (! $parent->hasChild($this->opening) || ! $parent->hasChild($this->closing)) {
            throw new \LogicException('HTML regions must begin and end as children of the same container.');
        }

        for ($node = $this->opening->nextSibling(); $node !== null; $node = $node->nextSibling()) {
            if ($node === $this->closing) {
                return;
            }
        }

        throw new \LogicException('HTML region boundaries must be ordered siblings.');
    }
}
