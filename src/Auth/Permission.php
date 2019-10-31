<?php

namespace Statamic\Auth;

class Permission
{
    protected $value;
    protected $placeholder;
    protected $placeholderLabel;
    protected $replacement;
    protected $callback;
    protected $children;
    protected $label;
    protected $translation;
    protected $description;
    protected $group;

    public function value(string $value = null)
    {
        if (is_null($value)) {
            return $this->value;
        }

        $this->value = $value;

        return $this;
    }

    public function withLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function originalLabel()
    {
        return $this->label;
    }

    public function withTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }

    public function translation()
    {
        return $this->translation;
    }

    public function label()
    {
        if (! $this->label && ! $this->translation) {
            return $this->value;
        }

        if ($this->label && !$this->placeholder) {
            return $this->label;
        }

        return __($this->translation ?? $this->label, [$this->placeholder => $this->placeholderLabel]);
    }

    public function placeholder()
    {
        return $this->placeholder;
    }

    public function withPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function withPlaceholderLabel($label)
    {
        $this->placeholderLabel = $label;

        return $this;
    }

    public function placeholderLabel()
    {
        return $this->placeholderLabel;
    }

    public function withReplacements(string $placeholder, callable $callback)
    {
        $this->placeholder = $placeholder;
        $this->callback = $callback;

        return $this;
    }

    public function withReplacement(string $replacement)
    {
        $this->replacement = $replacement;

        return $this;
    }

    public function replacement()
    {
        return $this->replacement;
    }

    public function callback()
    {
        return $this->callback;
    }

    public function permissions()
    {
        if (! $this->callback) {
            return collect([$this]);
        }

        // The callback should return an array where the keys are the replacements for the
        // permission values, and the values are the strings to be replaced inside the
        // labels. eg. ['blog' => 'Blog', 'downloads' => 'Downloadable Products']
        $items = call_user_func($this->callback);

        return collect($items)->map(function ($replacement) {
            $value = str_replace('{'.$this->placeholder.'}', $replacement['value'], $this->value());

            $replaced = (new self)
                ->value($value)
                ->withLabel($this->label)
                ->withTranslation($this->translation)
                ->withReplacement($replacement['value'])
                ->withPlaceholder($this->placeholder)
                ->withPlaceholderLabel($replacement['label'])
                ->inGroup($this->group());

            if ($this->children()) {
                $replaced->withChildren($this->children()->all());
            };

            return $replaced;
        })->values();
    }

    public function withChildren(array $children)
    {
        $this->children = collect($children)->map->inGroup($this->group);

        return $this;
    }

    public function children()
    {
        return $this->children ?? collect();
    }

    public function toTree()
    {
        return $this->permissions()->map(function ($permission) {
            $children = $permission->children();

            if ($permission->placeholder()) {
                $children = $children->map(function ($child) use ($permission) {
                    $value = str_replace('{'.$this->placeholder.'}', $permission->replacement(), $child->value());

                    return (new self)
                        ->value($value)
                        ->withLabel($child->originalLabel())
                        ->withTranslation($child->translation())
                        ->withReplacement($permission->replacement())
                        ->withPlaceholder($permission->placeholder())
                        ->withPlaceholderLabel($permission->placeholderLabel())
                        ->inGroup($permission->group());
                });
            }

            return [
                'value' => $permission->value(),
                'label' => $permission->label(),
                'description' => $permission->description(),
                'group' => $permission->group(),
                'children' => $children->flatMap->toTree()->all()
            ];
        })->all();
    }

    public function inGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    public function group()
    {
        return $this->group;
    }

    public function withDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function description()
    {
        return $this->description;
    }
}
