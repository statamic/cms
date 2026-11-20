<?php

namespace Statamic\View;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use RuntimeException;
use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\StaticCaching\NoCache\Region;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Throwable;

class Slot implements Htmlable
{
    protected array $params = [];

    protected bool $static = false;

    protected ?string $name = null;

    protected ?string $source = null;

    protected ?array $callerState = null;

    protected ?string $template = null;

    /**
     * @param  Closure(array): mixed  $renderer  Renders the slot's contents using the supplied data.
     * @param  array  $data  The scope the slot was defined in.
     */
    public function __construct(
        protected Closure $renderer,
        protected array $data = [],
    ) {
    }

    public static function forAntlers(array $nodes, string $source, array $data, NodeProcessor $processor): self
    {
        $state = static::captureCallerState();

        $slot = new self(static::antlersRenderer($nodes, $state, $processor), $data);

        $slot->source = $source;
        $slot->callerState = $state;

        return $slot;
    }

    public static function forBlade(string $template, array $data): self
    {
        // Prevents Blade::render from mistaking short slot content for a view name.
        $template = '{{-- slot --}}'.$template;

        $slot = new self(fn (array $slotData) => Blade::render($template, $slotData), $data);

        $slot->template = $template;

        return $slot;
    }

    protected static function captureCallerState(): array
    {
        return [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState];
    }

    protected static function antlersRenderer(array $nodes, array $callerState, NodeProcessor $processor): Closure
    {
        return function (array $data) use ($nodes, $callerState, $processor) {
            $tagState = static::captureCallerState();

            [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState] = $callerState;

            try {
                return $processor->cloneProcessor()->setData($data)->reduce($nodes);
            } finally {
                [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState] = $tagState;
            }
        };
    }

    protected static function textRenderer(string $source, array $callerState): Closure
    {
        return function (array $data) use ($source, $callerState) {
            $tagState = static::captureCallerState();

            [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState] = $callerState;

            try {
                return (string) app(ParserContract::class)->parse($source, $data);
            } finally {
                [GlobalRuntimeState::$isCascadeEnabled, GlobalRuntimeState::$prefixState] = $tagState;
            }
        };
    }

    public function render(array $props = []): string
    {
        if ($this->static && $props !== []) {
            throw new RuntimeException("The {$this->displayName()} has already been rendered and cached, so data can no longer be passed to it. This usually happens when a scoped slot is rendered inside a nocache region.");
        }

        $data = array_merge($this->data, ['params' => $this->params], $props);

        return trim((string) ($this->renderer)($data));
    }

    public function withParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function named(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public static function output(mixed $slot, array $props = []): string
    {
        if ($slot instanceof self) {
            return $slot->render($props);
        }

        return e($slot);
    }

    public function __serialize(): array
    {
        if ($this->source === null && $this->template === null) {
            return ['name' => $this->name, 'rendered' => $this->render()];
        }

        try {
            return array_filter([
                'name' => $this->name,
                'antlers' => $this->source,
                'state' => $this->callerState,
                'template' => $this->template,
                'data' => serialize(Region::filterCacheable($this->data)),
                'params' => serialize(Region::filterCacheable($this->params)),
            ], fn ($value) => $value !== null);
        } catch (Throwable $e) {
            throw new RuntimeException("The {$this->displayName()} cannot be cached because its scope contains values that cannot be serialized{$this->unserializableKeys()}.", previous: $e);
        }
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'] ?? null;

        if (array_key_exists('rendered', $data)) {
            $rendered = $data['rendered'];

            $this->data = [];
            $this->params = [];
            $this->renderer = fn () => $rendered;
            $this->static = true;

            return;
        }

        $this->data = unserialize($data['data']);
        $this->params = unserialize($data['params']);

        if (isset($data['template'])) {
            $this->template = $data['template'];
            $this->renderer = fn (array $slotData) => Blade::render($this->template, $slotData);

            return;
        }

        $this->source = $data['antlers'];
        $this->callerState = $data['state'];
        $this->renderer = static::textRenderer($this->source, $this->callerState);
    }

    protected function unserializableKeys(): string
    {
        $keys = collect(Region::filterCacheable(array_merge($this->data, $this->params)))
            ->filter(function ($value) {
                try {
                    serialize($value);

                    return false;
                } catch (Throwable) {
                    return true;
                }
            })
            ->keys();

        return $keys->isEmpty() ? '' : ' ('.$keys->implode(', ').')';
    }

    protected function displayName(): string
    {
        return $this->name ? "[{$this->name}] slot" : 'slot';
    }
}
