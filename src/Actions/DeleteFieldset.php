<?php

namespace Statamic\Actions;

use Statamic\Fields\Blueprint;
use Statamic\Fields\Fieldset;
use Statamic\Support\Str;

use function Statamic\trans as __;

class DeleteFieldset extends Delete
{
    public function visibleTo($item)
    {
        return $item instanceof Fieldset && $item->isDeletable();
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $item)
    {
        return $user->can('delete', $item);
    }

    public function redirect($items, $values)
    {
        return null;
    }

    public function warningText()
    {
        $fieldset = $this->items->first();
        $importedBy = $this->getImports($fieldset);

        if ($importedBy->isNotEmpty()) {
            return __('This fieldset is used in the following items: :items', [
                'items' => $importedBy->map(function ($items, $group) {
                    return $group.': '.$items->pluck('title')->implode(', ');
                })->implode('; '),
            ]);
        }
    }

    public function run($items, $values)
    {
        $fieldset = $items->first();
        $importedBy = $this->getImports($fieldset);

        if ($importedBy->isNotEmpty()) {
            throw new \Exception('Cannot be deleted because it is imported by other items');
        }

        $items->each->delete();

        return trans_choice('Item deleted|Items deleted', 1);
    }

    private function getImports(Fieldset $fieldset)
    {
        return collect($fieldset->importedBy())->flatten(1)->mapToGroups(function ($item) {
            return [$this->group($item) => ['handle' => $item->handle(), 'title' => $item->title()]];
        });
    }

    private function group(Blueprint|Fieldset $item)
    {
        if ($item instanceof Fieldset) {
            return __('Fieldsets');
        }

        if ($namespace = $item->namespace()) {
            return match (Str::before($namespace, '.')) {
                'collections' => __('Collections'),
                'taxonomies' => __('Taxonomies'),
                'navigation' => __('Navigation'),
                'globals' => __('Globals'),
                'assets' => __('Asset Containers'),
                'forms' => __('Forms'),
            };
        }

        return match ($item->handle()) {
            'user', 'user_group' => __('Users'),
            default => __('Other'),
        };
    }
}
