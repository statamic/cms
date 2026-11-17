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
        private readonly ?NodeProcessor $processor = null,
        private readonly int $processorDepth = 0,
    ) {
    }

    public static function antlersNode(
        AbstractNode $node,
        ?NodeProcessor $processor = null,
        int $processorDepth = 0
    ): self {
        $view = GlobalRuntimeState::$currentExecutionFile;

        return new self(
            self::ENGINE_ANTLERS,
            self::KIND_NODE,
            trim((string) $node->content),
            is_string($view) && $view !== '' ? $view : null,
            $node->startPosition->line ?? null,
            $node,
            $processor,
            $processorDepth,
        );
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    public function meta(?string $key = null): mixed
    {
        $meta = ['processor' => $this->processor, 'processor_depth' => $this->processorDepth];

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    public function scope(): ?array
    {
        if ($this->scopeCaptured) {
            return $this->capturedScope;
        }

        $this->scopeCaptured = true;

        if ($this->processor !== null) {
            return $this->capturedScope = $this->processor->getActiveData();
        }

        return null;
    }
}
