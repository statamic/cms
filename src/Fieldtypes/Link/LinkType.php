<?php

namespace Statamic\Fieldtypes\Link;

use Statamic\Fields\Field;
use Statamic\Support\Str;

use function Statamic\trans as __;

abstract class LinkType
{
    protected static ?string $title = null;

    protected string $handle;
    protected ?string $icon = null;

    public function handle(): string
    {
        return $this->handle;
    }

    public function setHandle(string $handle): static
    {
        $this->handle = $handle;

        return $this;
    }

    public function title(): string
    {
        if (static::$title) {
            return __(static::$title);
        }

        return __(Str::title(Str::humanize(static::handle())));
    }

    public function icon(): string
    {
        return $this->icon ?? 'link';
    }

    public function configFieldItems(): array
    {
        return [];
    }

    abstract public function resolve(string $id, $parent = null, bool $localize = false): mixed;

    abstract public function fieldtype(Field $field): ?array;

    public function visible(Field $field): bool
    {
        return true;
    }
}
