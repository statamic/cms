<?php

namespace Statamic\CommandPalette;

use Statamic\Support\Str;

abstract class Command
{
    protected $icon = 'entry';
    protected $keys;

    public function __construct(protected string|array $text, protected Category $category)
    {
        //
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function keys(string $keys): static
    {
        // TODO: Wire up keys API to frontend?

        $this->keys = $keys;

        return $this;
    }

    public function type(): string
    {
        return Str::snake((new \ReflectionClass(get_called_class()))->getShortName());
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type(),
            'category' => $this->category->value,
            'icon' => $this->icon,
            'keys' => $this->keys,
            'text' => $this->text,
        ];
    }
}
