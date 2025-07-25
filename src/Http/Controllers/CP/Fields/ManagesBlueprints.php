<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\DuplicateFieldException;
use Statamic\Exceptions\FieldsetRecursionException;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Statamic\Fields\FieldTransformer;
use Statamic\Support\Arr;
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
            return [Arr::pull($tab, 'handle') => [
                'display' => $tab['display'],
                'sections' => $this->tabSections($tab['sections']),
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

    private function validateRecursion($blueprint)
    {
        try {
            $blueprint->fields();
        } catch (FieldsetRecursionException $exception) {
            throw ValidationException::withMessages([
                'tabs' => __('statamic::validation.fieldset_imported_recursively', ['handle' => $exception->getFieldset()]),
            ]);
        }
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

    private function validateReservedFieldHandles($blueprint)
    {
        $handles = $blueprint->fields()->all()->keys();

        if ($handles->contains('id')) {
            throw ValidationException::withMessages([
                'tabs' => __('statamic::validation.reserved_field_handle', ['handle' => 'id']),
            ]);
        }
    }

    private function updateBlueprint($request, $blueprint)
    {
        $this->setBlueprintContents($request, $blueprint);

        $this->validateRecursion($blueprint);

        $this->validateUniqueHandles($blueprint);

        $this->validateReservedFieldHandles($blueprint);

        $blueprint->save();
    }

    private function tabSections(array $sections)
    {
        return collect($sections)->map(function ($section) {
            return Arr::removeNullValues([
                'display' => $section['display'] ?? null,
                'instructions' => $section['instructions'] ?? null,
                'collapsible' => ($collapsible = $section['collapsible']) ?: null,
                'collapsed' => ($collapsible && $section['collapsed']) ?: null,
                'fields' => collect($section['fields'])
                    ->map(fn ($field) => FieldTransformer::fromVue($field))
                    ->all(),
            ]);
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
            'sections' => $tab->sections()->map(function ($section, $i) use ($tab) {
                return array_merge($this->sectionToVue($section, $i, $tab), ['_id' => $tab->handle().'-'.$i]);
            })->values()->all(),
        ];
    }

    private function sectionToVue($section, $sectionIndex, $tab): array
    {
        return Arr::removeNullValues([
            'display' => $section->display(),
            'instructions' => $section->instructions(),
            'collapsible' => $section->collapsible(),
            'collapsed' => $section->collapsed(),
        ]) + [
            'fields' => collect($section->contents()['fields'] ?? [])->map(function ($field, $i) use ($tab, $sectionIndex) {
                return array_merge(FieldTransformer::toVue($field), ['_id' => $tab->handle().'-'.$sectionIndex.'-'.$i]);
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
