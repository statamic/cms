<?php

namespace Statamic\CommandPalette;

abstract class Command
{
    protected $icon = 'entry';

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
            'text' => $this->text,
        ];
    }
}
