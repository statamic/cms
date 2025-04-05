<?php

namespace Statamic\CommandPalette;

abstract class Command
{
    protected $icon = 'entry';
    protected $keys;

    public function __construct(protected string $text, protected Category $category)
    {
        //
    }

    // public function keyboardShortcut()
    // {
    //     // TODO: Implement keyboard shortcut configuration...
    // }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function keys(string $keys): self
    {
        $this->keys = $keys;

        return $this;
    }

    public function type(): string
    {
        return strtolower((new \ReflectionClass(get_called_class()))->getShortName());
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type(),
            'category' => $this->category->name,
            'icon' => $this->icon,
            'keys' => $this->keys,
            'text' => $this->text,
        ];
    }
}
