<?php

namespace Tests\View\Instrumentation\Support;

use Illuminate\Support\Collection;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TracerContract;

class RecordingSpanTracer implements TracerContract
{
    public array $events = [];

    protected int $handles = 0;

    public function onEnter(Span $span): mixed
    {
        $this->handles++;
        $handle = 'handle-'.$this->handles;
        $this->events[] = ['type' => 'enter', 'span' => $span, 'handle' => $handle];

        return $handle;
    }

    public function onExit(Span $span, mixed $handle, mixed $output): void
    {
        $this->events[] = ['type' => 'exit', 'span' => $span, 'handle' => $handle, 'output' => $output];
    }

    public function onRenderComplete(): void
    {
        $this->events[] = ['type' => 'complete'];
    }

    public function eventsOfType(string $type): Collection
    {
        return collect($this->events)->where('type', $type);
    }

    public function indexOfEvent(string $type, callable $matchesSpan): ?int
    {
        foreach ($this->eventsOfType($type) as $index => $event) {
            if ($matchesSpan($event['span'])) {
                return $index;
            }
        }

        return null;
    }

    public function exitFor(Span $span): ?array
    {
        $index = $this->indexOfEvent('exit', fn (Span $candidate) => $candidate === $span);

        if ($index === null) {
            return null;
        }

        return ['index' => $index] + $this->events[$index];
    }

    public function lastEventType(): ?string
    {
        if (empty($this->events)) {
            return null;
        }

        return $this->events[array_key_last($this->events)]['type'];
    }
}
