<?php

namespace Statamic\Auth;

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

    public function label()
    {
        if ($this->label && !$this->placeholder) {
            return $this->label;
        }

        $permission = $this->getTranslationPermission();

        $key = $this->getTranslationKey($permission);

        $translation = __($key, $this->getTranslationReplacements());

        return $key === $translation ? title_case($permission->value) : $translation;
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

    public function permissions()
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

        return $permissions;
    }

    protected function replacedPermissionFromItem($permission, $replacement, $label)
    {
        $value = str_replace('{'.$permission->placeholder.'}', $replacement, $permission->value);

        return (new self)
            ->value($value)
            ->withLabel($permission->label)
            ->inGroup($permission->group())
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
        if ($this->description && !$this->placeholder) {
            return $this->description;
        }

        $permission = $this->getTranslationPermission();

        $key = $this->getTranslationKey($permission).'_desc';

        $translation = __($key, $this->getTranslationReplacements());

        return $key === $translation ? null : $translation;
    }

    private function getTranslationPermission()
    {
        return $this->original ?? $this;
    }

    private function getTranslationReplacements()
    {
        if (! $this->original) {
            return [];
        }

        return [$this->original->placeholder() => $this->replacementLabel];
    }

    private function getTranslationKey($permission)
    {
        return 'statamic::permissions.' .  str_replace(' ', '_', $permission->value);
    }
}
