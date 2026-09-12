<?php

namespace Statamic\View\Instrumentation;

use LogicException;

class FieldDomTracer extends FieldTracer
{
    private static ?self $active = null;
    private array $instructions = [];
    private array $stack = [];
    private array $elements = [];
    private array $markers = [];
    private int $nextMarker = 0;

    public function capture(callable $render): mixed
    {
        if (self::$active === $this) {
            throw new LogicException('A field trace capture is already active.');
        }

        $previous = self::$active;
        self::$active = $this;
        $this->stack = [];
        $this->elements = [];
        $this->markers = [];
        $this->nextMarker = 0;

        try {
            return parent::capture($render);
        } finally {
            self::$active = $previous;
            $this->stack = [];
            $this->elements = [];
        }
    }

    public function instruction(string $action, ?int $target = null, bool $opaque = false): int
    {
        $this->instructions[] = [$action, $target, $opaque];

        return count($this->instructions);
    }

    public static function execute(int $instruction): string
    {
        return self::$active?->run($instruction) ?? '';
    }

    private function run(int $instruction): string
    {
        if (! isset($this->instructions[$instruction - 1])) {
            return '';
        }

        [$action, $target, $opaque] = $this->instructions[$instruction - 1];

        if ($action === 'element') {
            $this->nextMarker++;
            $this->elements[$instruction] = $this->nextMarker;

            return (string) $this->nextMarker;
        }

        if ($action === 'start') {
            if ($target === null) {
                $this->nextMarker++;
                $id = $this->nextMarker;
            } else {
                $id = $this->elements[$target] ?? null;
            }

            $this->stack[] = [
                'instruction' => $instruction,
                'id' => $id,
                'comment' => $target === null,
                'opaque' => $opaque,
            ];

            if ($target === null) {
                return '<!--statamic:field:'.$id.'-->';
            }

            return '';
        }

        $frame = end($this->stack);
        if (! $frame || $frame['instruction'] !== $target) {
            return '';
        }

        array_pop($this->stack);

        if ($frame['comment']) {
            return '<!--/statamic:field:'.$frame['id'].'-->';
        }

        return '';
    }

    protected function isObservingReads(): bool
    {
        return $this->stack !== [];
    }

    protected function fieldRead(int $field): void
    {
        $frame = end($this->stack);
        if ($frame && $frame['id'] !== null) {
            $this->markers[$frame['id']][$field] = $field;
        }
    }

    public function markers(): array
    {
        return array_map('array_values', $this->markers);
    }

    public function suppressesNestedMarkers(): bool
    {
        return end($this->stack)['opaque'] ?? false;
    }
}
