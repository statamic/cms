<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\FieldRepository;
use Statamic\Events\FieldsetCreated;
use Statamic\Events\FieldsetCreating;
use Statamic\Events\FieldsetDeleted;
use Statamic\Events\FieldsetDeleting;
use Statamic\Events\FieldsetReset;
use Statamic\Events\FieldsetSaved;
use Statamic\Events\FieldsetSaving;
use Statamic\Exceptions\FieldsetRecursionException;
use Statamic\Facades;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Path;
use Statamic\Facades\Taxonomy;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Fieldset
{
    protected $handle;
    protected $contents = [];
    protected $ensuredFields = [];
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
        $fields = Arr::get($contents, 'fields', []);

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
        return $this->getContents();
    }

    private function getContents()
    {
        $contents = $this->contents;

        $contents['fields'] = $contents['fields'] ?? [];

        if ($this->ensuredFields) {
            $contents = $this->addEnsuredFieldsToContents($contents, $this->ensuredFields);
        }

        return array_filter($contents);
    }

    private function addEnsuredFieldsToContents($contents, $ensuredFields)
    {
        foreach ($ensuredFields as $field) {
            $contents = $this->addEnsuredFieldToContents($contents, $field);
        }

        return $contents;
    }

    private function addEnsuredFieldToContents($contents, $ensured)
    {
        $imported = false;
        $handle = $ensured['handle'];
        $config = $ensured['config'];
        $prepend = $ensured['prepend'];

        $fields = collect($contents['fields'] ?? [])->keyBy(function ($field) {
            return (isset($field['import'])) ? 'import:'.($field['prefix'] ?? null).$field['import'] : $field['handle'];
        });

        $importedFields = $fields->filter(function ($field, $key) {
            return Str::startsWith($key, 'import:');
        })->keyBy(function ($field) {
            return ($field['prefix'] ?? null).$field['import'];
        })->mapWithKeys(function ($field, $partial) {
            return (new Fields([$field]))->all()->map(function ($field) use ($partial) {
                return compact('partial', 'field');
            });
        });

        // If a field with that handle is in the contents, its either an inline field or a referenced field...
        $existingField = $fields->get($handle);

        if ($exists = $existingField !== null) {
            if (is_string($existingField['field'])) {
                // If it's a string, then it's a reference field. We should merge any ensured config into the 'config'
                // override array, but only keys that don't already exist in the actual partial field's config.
                $referencedField = FieldRepository::find($existingField['field']);
                $referencedFieldConfig = $referencedField->config();
                $config = array_merge($config, $referencedFieldConfig);
                $config = Arr::except($config, array_keys($referencedFieldConfig));
                $field = ['handle' => $handle, 'field' => $existingField['field'], 'config' => $config];
            } else {
                // If it's not a string, then it's an inline field. We'll just merge the
                // config right into the field key, with the user defined config winning.
                $config = array_merge($config, $existingField['field']);
                $field = ['handle' => $handle, 'field' => $config];
            }
        } else {
            if ($importedField = $importedFields->get($handle)) {
                $importKey = 'import:'.$importedField['partial'];
                $field = $fields->get($importKey);
                $importedConfig = $importedField['field']->config();
                $config = array_merge($config, $importedConfig);
                $config = Arr::except($config, array_keys($importedConfig));
                $field['config'][$handle] = $config;
                $fields->put($importKey, $field);
                $imported = true;
            } else {
                $field = ['handle' => $handle, 'field' => $config];
            }
        }

        // Set the field config in it's proper place.
        if (! $imported) {
            if ($exists) {
                $fields->put($handle, $field);
            } elseif (! $exists && $prepend) {
                $fields->prepend($field);
            } else {
                $fields->push($field);
            }
        }

        $contents['fields'] = $fields->values()->all();

        return $contents;
    }

    public function title()
    {
        return $this->contents['title'] ?? Str::humanize(Str::of($this->handle)->after('::')->afterLast('.'));
    }

    /**
     * @throws FieldsetRecursionException
     */
    public function validateRecursion()
    {
        $this->fields();
    }

    public function fields(): Fields
    {
        $fields = Arr::get($this->contents(), 'fields', []);

        return new Fields($fields);
    }

    public function field(string $handle): ?Field
    {
        return $this->fields()->get($handle);
    }

    public function hasField(string $handle): bool
    {
        return $this->fields()->has($handle);
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

    public function resetUrl()
    {
        return cp_route('fieldsets.reset', $this->handle());
    }

    public function importedBy(): array
    {
        $blueprints = collect([
            ...Collection::all()->flatMap->entryBlueprints(),
            ...Taxonomy::all()->flatMap->termBlueprints(),
            ...GlobalSet::all()->map->blueprint(),
            ...AssetContainer::all()->map->blueprint(),
            ...Blueprint::in('')->values(),
        ])->filter()->filter(function (Blueprint $blueprint) {
            return collect($blueprint->contents()['tabs'])
                ->pluck('sections')
                ->flatten(1)
                ->pluck('fields')
                ->flatten(1)
                ->filter(fn ($field) => $field && $this->fieldImportsFieldset($field))
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

    public function isResettable()
    {
        return $this->isNamespaced()
            && File::exists(FieldsetRepository::overriddenNamespacedFieldsetPath($this->handle));
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

    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents && FieldsetDeleting::dispatch($this) === false) {
            return false;
        }

        FieldsetRepository::delete($this);

        if ($withEvents) {
            FieldsetDeleted::dispatch($this);
        }

        return true;
    }

    public function reset()
    {
        FieldsetRepository::reset($this);

        FieldsetReset::dispatch($this);

        return true;
    }

    public function ensureField($handle, $config, $prepend = false)
    {
        if (isset($this->ensuredFields[$handle])) {
            return $this;
        }

        $this->ensuredFields[$handle] = compact('handle', 'prepend', 'config');

        return $this;
    }

    public function ensureFieldPrepended($handle, $field)
    {
        return $this->ensureField($handle, $field, true);
    }

    public function ensureFieldHasConfig($handle, $config)
    {
        if (! $this->hasField($handle)) {
            return $this;
        }

        return $this->ensureField($handle, $config);
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Fieldset::{$method}(...$parameters);
    }
}
