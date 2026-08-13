<?php

namespace Statamic\View;

use Closure;
use Illuminate\Contracts\Support\Htmlable;

class Slot implements Htmlable
{
    protected array $params = [];

    /**
     * @param  Closure(array): mixed  $renderer  Renders the slot's contents using the supplied data.
     * @param  array  $data  The scope the slot was defined in.
     */
    public function __construct(
        protected Closure $renderer,
        protected array $data = [],
    ) {
    }

    public function render(array $props = []): string
    {
        $data = array_merge($this->data, ['params' => $this->params], $props);

        return trim((string) ($this->renderer)($data));
    }

    public function withParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __serialize(): array
    {
        return ['rendered' => $this->render()];
    }

    public function __unserialize(array $data): void
    {
        $rendered = $data['rendered'] ?? '';

        $this->renderer = fn () => $rendered;
        $this->data = [];
        $this->params = [];
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
}
