<?php

namespace Statamic\View\Instrumentation;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;

/** @internal */
final class Span
{
    public const ENGINE_ANTLERS = 'antlers';

    public const KIND_NODE = 'node';

    /** @var array<string, mixed>|null */
    private ?array $capturedScope = null;

    private bool $scopeCaptured = false;

    private function __construct(
        public readonly string $engine,
        public readonly string $kind,
        public readonly string $expression,
        public readonly ?string $view,
        public readonly ?int $line,
        private readonly mixed $raw,
        private readonly array $meta = [],
    ) {
    }

    public static function antlersNode(
        AbstractNode $node,
        ?NodeProcessor $processor = null,
        int $processorDepth = 0
    ): self {
        $view = GlobalRuntimeState::$currentExecutionFile;

        if (! is_string($view) || $view === '') {
            $view = null;
        }

        return new self(
            self::ENGINE_ANTLERS,
            self::KIND_NODE,
            trim((string) $node->content),
            $view,
            $node->startPosition->line ?? null,
            $node,
            ['processor' => $processor, 'processor_depth' => $processorDepth],
        );
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    public function meta(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->meta;
        }

        return $this->meta[$key] ?? null;
    }

    public function scope(): ?array
    {
        if ($this->scopeCaptured) {
            return $this->capturedScope;
        }

        $this->scopeCaptured = true;

        $processor = $this->meta['processor'] ?? null;

        if ($processor instanceof NodeProcessor) {
            return $this->capturedScope = $processor->getActiveData();
        }

        return null;
    }
}
