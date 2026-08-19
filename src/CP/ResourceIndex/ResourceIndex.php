<?php

namespace Statamic\CP\ResourceIndex;

use Closure;
use InvalidArgumentException;
use Statamic\Data\DataCollection;
use Statamic\Support\Str;

use function Statamic\trans as __;

class ResourceIndex
{
    protected string $handle;
    protected string|Closure $title;
    protected string|Closure $itemLabel;
    protected string|Closure $icon;
    protected DataCollection $items;
    protected array|Closure $defaultGroups = [];
    protected string|Closure $fallbackLabel;

    public function __construct(string $handle, iterable $items = [])
    {
        $this->handle = $handle;
        $this->title = fn () => __(Str::headline($handle));
        $this->itemLabel = fn () => __(Str::singular(Str::headline($handle)));
        $this->icon = $handle;
        $this->fallbackLabel = fn () => __('Other');
        $this->items($items);
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function title(string|Closure|null $title = null): string|static
    {
        if (func_num_args() === 0) {
            return value($this->title);
        }

        $this->title = $title ?? fn () => __(Str::headline($this->handle));

        return $this;
    }

    public function itemLabel(string|Closure|null $label = null): string|static
    {
        if (func_num_args() === 0) {
            return value($this->itemLabel);
        }

        $this->itemLabel = $label ?? fn () => __(Str::singular(Str::headline($this->handle)));

        return $this;
    }

    public function icon(string|Closure|null $icon = null): string|static
    {
        if (func_num_args() === 0) {
            return value($this->icon);
        }

        $this->icon = $icon ?? $this->handle;

        return $this;
    }

    public function items(iterable $items): static
    {
        $this->items = DataCollection::make($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    $item = $item->toArray();
                }

                if (! isset($item['id'], $item['title'])) {
                    throw new InvalidArgumentException("Items in resource index [{$this->handle}] require id and title values.");
                }

                return $item;
            })
            ->values();

        return $this;
    }

    public function all(): DataCollection
    {
        return $this->items;
    }

    public function defaultGroups(array|Closure|null $groups = null): array|static
    {
        if (func_num_args() === 0) {
            return $this->resolveDefaultGroups();
        }

        $this->defaultGroups = $groups ?? [];

        return $this;
    }

    public function resolveDefaultGroups(): array
    {
        $groups = $this->defaultGroups;

        if ($groups instanceof Closure) {
            $groups = value($groups, $this->items);
        }

        return collect($groups)->values()->all();
    }

    public function fallbackLabel(string|Closure|null $label = null): string|static
    {
        if (func_num_args() === 0) {
            return value($this->fallbackLabel);
        }

        $this->fallbackLabel = $label ?? fn () => __('Other');

        return $this;
    }

    public function render(Closure $response)
    {
        return app(ResourceIndexRepository::class)->render($this, $response);
    }
}
