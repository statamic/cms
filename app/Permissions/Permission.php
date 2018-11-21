<?php

namespace Statamic\Permissions;

class Permission
{
    protected $value;
    protected $placeholder;
    protected $callback;
    protected $original;
    protected $replacement;
    protected $replacementLabel;
    protected $children;
    protected $label;

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

    public function label()
    {
        $permission = $this;
        $replacements = [];

        if ($this->original) {
            $permission = $this->original;
            $replacements = [$this->original->placeholder() => $this->replacementLabel];
        }

        $key = $this->label
            ? $this->label
            : 'statamic::messages.permission_' .  str_replace(' ', '_', $permission->value);

        if ($key !== ($translation = __($key, $replacements))) {
            return $translation;
        }

        return title_case($permission->value);
    }

    public function placeholder()
    {
        return $this->placeholder;
    }

    public function withReplacements(string $placeholder, callable $callback)
    {
        $this->placeholder = $placeholder;
        $this->callback = $callback;

        return $this;
    }

    public function callback()
    {
        return $this->callback;
    }

    public function permissions($withChildren = false)
    {
        $permissions = collect([$this]);

        if ($this->callback) {
            // The callback should return an array where the keys are the replacements for the
            // permission values, and the values are the strings to be replaced inside the
            // labels. eg. ['blog' => 'Blog', 'downloads' => 'Downloadable Products']
            $items = call_user_func($this->callback);

            $permissions = collect($items)->map(function ($replacement) {
                return $this->replacedPermissionFromItem(
                    $this, $replacement['value'], $replacement['label']
                );
            })->values();
        }

        if ($withChildren) {
            $permissions = $permissions->merge($this->children());
        }

        return $permissions;
    }

    protected function replacedPermissionFromItem($permission, $replacement, $label)
    {
        $value = str_replace('{'.$permission->placeholder.'}', $replacement, $permission->value);

        return (new self)
            ->value($value)
            ->withLabel($permission->label)
            ->replaces($permission, $replacement, $label);
    }

    public function replaces($original, $replacement, $label)
    {
        $this->original = $original;
        $this->replacement = $replacement;
        $this->replacementLabel = $label;

        return $this;
    }

    public function original()
    {
        return $this->original;
    }

    public function withChildren(array $children)
    {
        $this->children = collect($children);

        return $this;
    }

    public function children()
    {
        $children = $this->children ?? collect();

        if ($this->placeholder) {
            $children = $children->map(function ($child) {
                return $child->withReplacements($this->placeholder, $this->callback);
            });
        }

        return $children;
    }

    public function toTree()
    {
        if ($this->original && $this->original->children) {
            $children = $this->original->children();
        } else {
            $children = $this->children();
        }

        if ($this->replacement) {
            $children = $children->map(function ($child) {
                return $this->replacedPermissionFromItem(
                    $child,
                    $this->replacement,
                    $this->replacementLabel
                );
            });
        }

        return [
            'permission' => $this,
            'children' => $children->map->toTree()
        ];
    }
}
