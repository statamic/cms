<?php

namespace Statamic\Fields;

use Statamic\Events\FieldsetCreated;
use Statamic\Events\FieldsetCreating;
use Statamic\Events\FieldsetDeleted;
use Statamic\Events\FieldsetSaved;
use Statamic\Events\FieldsetSaving;
use Statamic\Facades;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Path;
use Statamic\Facades\Taxonomy;
use Statamic\Support\Str;

class Fieldset
{
    protected $handle;
    protected $contents = [];
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;
    protected $initialPath;

    public function setHandle(string $handle)
    {
        $this->handle = $handle;

        return $this;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    public function path()
    {
        return Path::tidy(vsprintf('%s/%s.yaml', [
            Facades\Fieldset::directory(),
            str_replace('.', '/', $this->handle()),
        ]));
    }

    public function initialPath($path = null)
    {
        if (func_num_args() === 0) {
            return $this->initialPath;
        }

        $this->initialPath = $path;

        return $this;
    }

    public function setContents(array $contents)
    {
        $fields = array_get($contents, 'fields', []);

        // Support legacy syntax
        if (! empty($fields) && array_keys($fields)[0] !== 0) {
            $fields = collect($fields)->map(function ($field, $handle) {
                return compact('handle', 'field');
            })->values()->all();
        }

        $contents['fields'] = $fields;

        $this->contents = $contents;

        return $this;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function title()
    {
        return $this->contents['title'] ?? Str::humanize(Str::of($this->handle)->after('::')->afterLast('.'));
    }

    public function fields(): Fields
    {
        $fields = array_get($this->contents, 'fields', []);

        return new Fields($fields);
    }

    public function field(string $handle): ?Field
    {
        return $this->fields()->get($handle);
    }

    public function isNamespaced(): bool
    {
        return Str::contains($this->handle(), '::');
    }

    public function namespace()
    {
        return $this->isNamespaced() ? Str::before($this->handle, '::') : null;
    }

    public function editUrl()
    {
        return cp_route('fieldsets.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('fieldsets.destroy', $this->handle());
    }

    public function importedBy(): array
    {
        $blueprints = collect([
            ...Collection::all()->flatMap->entryBlueprints(),
            ...Taxonomy::all()->flatMap->termBlueprints(),
            ...GlobalSet::all()->map->blueprint(),
            ...AssetContainer::all()->map->blueprint(),
            ...Blueprint::in('')->values(),
        ])->filter(function (Blueprint $blueprint) {
            return collect($blueprint->contents()['tabs'])
                ->pluck('sections')
                ->flatten(1)
                ->pluck('fields')
                ->flatten(1)
                ->filter(fn ($field) => $this->fieldImportsFieldset($field))
                ->isNotEmpty();
        })->values();

        $fieldsets = \Statamic\Facades\Fieldset::all()
            ->filter(fn (Fieldset $fieldset) => isset($fieldset->contents()['fields']))
            ->filter(function (Fieldset $fieldset) {
                return collect($fieldset->contents()['fields'])
                    ->filter(fn ($field) => $this->fieldImportsFieldset($field))
                    ->isNotEmpty();
            })
            ->values();

        return ['blueprints' => $blueprints, 'fieldsets' => $fieldsets];
    }

    private function fieldImportsFieldset(array $field): bool
    {
        if (isset($field['import'])) {
            return $field['import'] === $this->handle();
        }

        if (is_string($field['field'])) {
            return Str::before($field['field'], '.') === $this->handle();
        }

        if (isset($field['field']['fields'])) {
            return collect($field['field']['fields'])
                ->filter(fn ($field) => $this->fieldImportsFieldset($field))
                ->isNotEmpty();
        }

        if (isset($field['field']['sets'])) {
            return collect($field['field']['sets'])
                ->filter(fn ($setGroup) => isset($setGroup['sets']))
                ->filter(function ($setGroup) {
                    return collect($setGroup['sets'])->filter(function ($set) {
                        return collect($set['fields'])
                            ->filter(fn ($field) => $this->fieldImportsFieldset($field))
                            ->isNotEmpty();
                    })->isNotEmpty();
                })
                ->isNotEmpty();
        }

        return false;
    }

    public function isDeletable()
    {
        return ! $this->isNamespaced();
    }

    public function afterSave($callback)
    {
        $this->afterSaveCallbacks[] = $callback;

        return $this;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save()
    {
        $isNew = is_null(Facades\Fieldset::find($this->handle()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && FieldsetCreating::dispatch($this) === false) {
                return false;
            }

            if (FieldsetSaving::dispatch($this) === false) {
                return false;
            }
        }

        FieldsetRepository::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                FieldsetCreated::dispatch($this);
            }

            FieldsetSaved::dispatch($this);
        }

        return $this;
    }

    public function delete()
    {
        FieldsetRepository::delete($this);

        FieldsetDeleted::dispatch($this);

        return true;
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Fieldset::{$method}(...$parameters);
    }
}
