<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\LazyCollection;

/** @internal */
class NodeSequence
{
    protected Document $document;

    /** @var \Closure(bool): iterable<Node> */
    protected \Closure $iterate;

    protected function __construct(Document $document, \Closure $iterate)
    {
        $this->document = $document;
        $this->iterate = $iterate;
    }

    public static function childrenOf(Document $document, int $parent): self
    {
        return new self($document, function ($reverse) use ($document, $parent) {
            $arena = $document->arena();
            $node = self::firstChildForDirection($arena, $parent, $reverse);

            while ($node !== null) {
                yield $document->handle($node);
                $node = self::nextArenaSibling($arena, $node, $reverse);
            }
        });
    }

    public static function between(AntlersNode $opening, AntlersNode $closing): self
    {
        if ($opening->document() !== $closing->document()) {
            throw new \InvalidArgumentException('HTML sequence boundaries must belong to the same document.');
        }

        $document = $opening->document();

        return new self($document, function ($reverse) use ($opening, $closing) {
            [$boundary, $node] = self::regionTraversalStart($opening, $closing, $reverse);

            while ($node !== $boundary) {
                if ($node === null) {
                    throw new \LogicException('HTML sequence boundaries must be ordered siblings.');
                }

                yield $node;
                $node = self::nextObjectSibling($node, $reverse);
            }
        });
    }

    protected static function firstChildForDirection(Arena $arena, int $parent, bool $reverse): ?int
    {
        if ($reverse) {
            return $arena->lastChild($parent);
        }

        return $arena->firstChild($parent);
    }

    protected static function nextArenaSibling(Arena $arena, int $node, bool $reverse): ?int
    {
        if ($reverse) {
            return $arena->previous($node);
        }

        return $arena->next($node);
    }

    protected static function regionTraversalStart(AntlersNode $opening, AntlersNode $closing, bool $reverse): array
    {
        if ($reverse) {
            return [$opening, $closing->previousSibling()];
        }

        return [$closing, $opening->nextSibling()];
    }

    protected static function nextObjectSibling(Node $node, bool $reverse): ?Node
    {
        if ($reverse) {
            return $node->previousSibling();
        }

        return $node->nextSibling();
    }

    public function document(): Document
    {
        return $this->document;
    }

    /** @return LazyCollection<int, Node> */
    public function nodes(): LazyCollection
    {
        return LazyCollection::make(fn () => ($this->iterate)(false));
    }

    /** @return LazyCollection<int, Element> */
    public function elements(bool $deep = true): LazyCollection
    {
        return LazyCollection::make(function () use ($deep) {
            foreach ($this->nodes() as $node) {
                if (! $node instanceof Element) {
                    continue;
                }

                yield $node;

                if ($deep) {
                    yield from $node->content()->elements();
                }
            }
        })->values();
    }

    /**
     * @param  callable(string, string, Text): (string|null)  $rewrite
     * @param  (callable(Element): bool)|null  $descend
     */
    public function rewriteTextEdges(callable $rewrite, ?callable $descend = null): static
    {
        $this->rewriteEdge(TextEdge::LEADING, $rewrite, $descend);
        $this->rewriteEdge(TextEdge::TRAILING, $rewrite, $descend);

        return $this;
    }

    protected function rewriteEdge(string $edge, callable $rewrite, ?callable $descend = null): bool
    {
        $reverse = $edge === TextEdge::TRAILING;

        foreach (($this->iterate)($reverse) as $node) {
            if ($node instanceof Text) {
                $content = $node->content();
                $replacement = $rewrite($content, $edge, $node);

                if ($replacement !== null && $replacement !== $content) {
                    $node->content((string) $replacement);
                    $content = (string) $replacement;
                }

                if ($content !== '') {
                    return true;
                }

                continue;
            }

            if ($node instanceof Element) {
                if (Document::isVoidElement($node->name())
                    || ($descend !== null && ! $descend($node))) {
                    return true;
                }

                if ($node->content()->rewriteEdge($edge, $rewrite, $descend)) {
                    return true;
                }

                continue;
            }

            if ($node instanceof AntlersNode || $node instanceof SourceAnchor) {
                return true;
            }
        }

        return false;
    }
}
