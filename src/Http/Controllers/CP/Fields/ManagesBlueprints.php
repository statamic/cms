<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\DuplicateFieldException;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Statamic\Fields\FieldTransformer;
use Statamic\Support\Str;

trait ManagesBlueprints
{
    private function indexItems(Collection $blueprints, $item)
    {
        return $blueprints->map(function ($blueprint) use ($item) {
            return [
                'id' => $blueprint->handle(),
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
                'tabs' => $blueprint->tabs()->count(),
                'fields' => $blueprint->fields()->all()->count(),
                'hidden' => $blueprint->hidden(),
                'edit_url' => $this->editUrl($item, $blueprint),
                'delete_url' => $this->deleteUrl($item, $blueprint),
            ];
        })->values();
    }

    private function setBlueprintContents(Request $request, Blueprint $blueprint)
    {
        $tabs = collect($request->tabs)->mapWithKeys(function ($tab) {
            return [array_pull($tab, 'handle') => [
                'display' => $tab['display'],
                'fields' => $this->tabFields($tab['fields']),
            ]];
        })->all();

        $blueprint
            ->setHidden($request->hidden)
            ->setContents(array_merge($blueprint->contents(), array_filter([
                'title' => $request->title,
                'tabs' => $tabs,
            ])));

        return $blueprint;
    }

    private function validateUniqueHandles($blueprint)
    {
        try {
            $blueprint->validateUniqueHandles();
        } catch (DuplicateFieldException $exception) {
            throw ValidationException::withMessages([
                'tabs' => __('statamic::validation.duplicate_field_handle', ['handle' => $exception->getHandle()]),
            ]);
        }
    }

    private function updateBlueprint($request, $blueprint)
    {
        $this->setBlueprintContents($request, $blueprint);

        $this->validateUniqueHandles($blueprint);

        $blueprint->save();
    }

    private function tabFields(array $fields)
    {
        return collect($fields)->map(function ($field) {
            return FieldTransformer::fromVue($field);
        })->all();
    }

    private function toVueObject(Blueprint $blueprint): array
    {
        return [
            'title' => $blueprint->title(),
            'handle' => $blueprint->handle(),
            'hidden' => $blueprint->hidden(),
            'tabs' => $blueprint->tabs()->map(function ($tab, $i) {
                return array_merge($this->tabToVue($tab), ['_id' => $i]);
            })->values()->all(),
        ];
    }

    private function tabToVue($tab): array
    {
        return [
            'handle' => $tab->handle(),
            'display' => $tab->display(),
            'fields' => collect($tab->contents()['fields'])->map(function ($field, $i) {
                return array_merge(FieldTransformer::toVue($field), ['_id' => $i]);
            })->all(),
        ];
    }

    private function storeBlueprint(Request $request, string $namespace)
    {
        $handle = Str::slug($request->title, '_');

        if (Facades\Blueprint::in($namespace)->has($handle)) {
            throw ValidationException::withMessages(['title' => __('A blueprint with that name already exists.')]);
        }

        $blueprint = (new Blueprint)
            ->setHandle($handle)
            ->setNamespace($namespace)
            ->setContents([
                'title' => $request->title,
                'tabs' => [
                    'main' => [
                        'display' => __('Main'),
                        'fields' => [],
                    ],
                ],
            ]);

        $blueprint->save();

        return $blueprint;
    }
}
