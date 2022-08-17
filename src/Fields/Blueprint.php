<?php

namespace Statamic\Fields;

use ArrayAccess;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\QueryableValue;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\BlueprintCreated;
use Statamic\Events\BlueprintDeleted;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\BlueprintSaving;
use Statamic\Exceptions\DuplicateFieldException;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Path;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Blueprint implements Augmentable, QueryableValue, ArrayAccess, Arrayable
{
    use HasAugmentedData, ExistsAsFile;

    protected $handle;
    protected $namespace;
    protected $order;
    protected $hidden = false;
    protected $initialPath;
    protected $contents;
    protected $fieldsCache;
    protected $parent;
    protected $ensuredFields = [];
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

    public function setHandle(string $handle)
    {
        $this->handle = $handle;

        return $this;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    public function setNamespace(?string $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function namespace(): ?string
    {
        return $this->namespace;
    }

    public function setOrder($order)
    {
        if (! is_null($order)) {
            $order = (int) $order;
        }

        $this->order = $order;

        return $this;
    }

    public function order()
    {
        return $this->order;
    }

    public function setHidden(?bool $hidden)
    {
        if (is_null($hidden)) {
            $hidden = false;
        }

        $this->hidden = $hidden;

        return $this;
    }

    public function hidden()
    {
        return $this->hidden;
    }

    public function setInitialPath(string $path)
    {
        $this->initialPath = $path;

        return $this;
    }

    public function initialPath()
    {
        return $this->initialPath;
    }

    public function path()
    {
        return Path::tidy(vsprintf('%s/%s/%s.yaml', [
            Facades\Blueprint::directory(),
            str_replace('.', '/', (string) $this->namespace()),
            $this->handle(),
        ]));
    }

    public function setContents(array $contents)
    {
        $this->contents = $contents;

        return $this
            ->normalizeSections()
            ->resetFieldsCache();
    }

    public function contents(): array
    {
        return Blink::once($this->contentsBlinkKey(), function () {
            return $this->getContents();
        });
    }

    private function contentsBlinkKey()
    {
        return "blueprint-contents-{$this->namespace()}-{$this->handle()}";
    }

    private function fieldsBlinkKey()
    {
        return "blueprint-fields-{$this->namespace()}-{$this->handle()}";
    }

    private function getContents()
    {
        $contents = $this->contents;

        $contents['sections'] = $contents['sections'] ?? [
            'main' => ['fields' => []],
        ];

        if ($this->ensuredFields) {
            $contents = $this->addEnsuredFieldsToContents($contents, $this->ensuredFields);
        }

        return array_filter(
            array_merge([
                'hide' => $this->hidden,
                'order' => $this->order,
            ], $contents)
        );
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
        $section = $ensured['section'] ?? array_keys($contents['sections'])[0] ?? 'main';
        $prepend = $ensured['prepend'];

        $sections = collect($contents['sections']);

        // Get all the fields, and mark which section they're in.
        $allFields = $sections->flatMap(function ($section, $sectionHandle) {
            return collect($section['fields'] ?? [])->keyBy(function ($field) {
                return (isset($field['import'])) ? 'import:'.($field['prefix'] ?? null).$field['import'] : $field['handle'];
            })->map(function ($field) use ($sectionHandle) {
                $field['section'] = $sectionHandle;

                return $field;
            });
        });

        $importedFields = $allFields->filter(function ($field, $key) {
            return Str::startsWith($key, 'import:');
        })->keyBy(function ($field) {
            return ($field['prefix'] ?? null).$field['import'];
        })->mapWithKeys(function ($field, $partial) {
            return (new Fields([$field]))->all()->map(function ($field) use ($partial) {
                return compact('partial', 'field');
            });
        });

        // If a field with that handle is in the contents, its either an inline field or a referenced field...
        $existingField = $allFields->get($handle);
        if ($exists = $existingField !== null) {
            // Since it already exists, maintain the position in that section.
            $section = $existingField['section'];

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
        }
        $fields = collect($sections[$section]['fields'] ?? [])->keyBy(function ($field) {
            return (isset($field['import'])) ? 'import:'.($field['prefix'] ?? null).$field['import'] : $field['handle'];
        });

        if (! $exists) {
            if ($importedField = $importedFields->get($handle)) {
                $importKey = 'import:'.$importedField['partial'];
                $field = $allFields->get($importKey);
                $section = $field['section'];
                $fields = collect($sections[$section]['fields'])->keyBy(function ($field) {
                    return (isset($field['import'])) ? 'import:'.$field['import'] : $field['handle'];
                });
                $importedConfig = $importedField['field']->config();
                $config = array_merge($config, $importedConfig);
                $config = Arr::except($config, array_keys($importedConfig));
                $field['config'][$handle] = $config;
                unset($field['section']);
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

        $contents['sections'][$section]['fields'] = $fields->values()->all();

        return $contents;
    }

    public function fileData()
    {
        return $this->contents();
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        $this->resetFieldsCache();

        return $this;
    }

    public function parent()
    {
        return $this->parent;
    }

    public function sections(): Collection
    {
        return collect(Arr::get($this->contents(), 'sections', []))->map(function ($contents, $handle) {
            return (new Section($handle))->setContents($contents);
        });
    }

    public function fields(): Fields
    {
        if ($this->fieldsCache) {
            return $this->fieldsCache;
        }

        $fn = function () {
            $this->validateUniqueHandles();

            return new Fields($this->sections()->map->fields()->flatMap->items());
        };

        $fields = $this->handle() ? Blink::once($this->fieldsBlinkKey(), $fn) : $fn();

        $fields->setParent($this->parent);

        $this->fieldsCache = $fields;

        return $fields;
    }

    public function hasField($field)
    {
        return $this->fields()->has($field);
    }

    public function hasSection($section)
    {
        return $this->sections()->has($section);
    }

    public function hasFieldInSection($field, $section)
    {
        if ($section = $this->sections()->get($section)) {
            return $section->fields()->has($field);
        }

        return false;
    }

    public function field($field)
    {
        return $this->fields()->get($field);
    }

    public function columns()
    {
        $columns = $this->fields()
            ->all()
            ->values()
            ->map(function ($field, $index) {
                return Column::make()
                    ->field($field->handle())
                    ->fieldtype($field->fieldtype()->indexComponent())
                    ->label(__($field->display()))
                    ->listable($field->isListable())
                    ->defaultVisibility($field->isVisibleOnListing())
                    ->visible($field->isVisibleOnListing())
                    ->sortable($field->isSortable())
                    ->defaultOrder($index + 1);
            })
            ->keyBy('field');

        return new Columns($columns);
    }

    public function isEmpty(): bool
    {
        return $this->fields()->all()->isEmpty();
    }

    public function title()
    {
        return array_get($this->contents, 'title', Str::humanize($this->handle));
    }

    public function toPublishArray()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
            'sections' => $this->sections()->map->toPublishArray()->values()->all(),
            'empty' => $this->isEmpty(),
        ];
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
        $name = Str::removeLeft($this->namespace().'.'.$this->handle(), '.');
        $isNew = is_null(Facades\Blueprint::find($name));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if (BlueprintSaving::dispatch($this) === false) {
                return false;
            }
        }

        BlueprintRepository::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                BlueprintCreated::dispatch($this);
            }

            BlueprintSaved::dispatch($this);
        }

        return $this;
    }

    public function delete()
    {
        BlueprintRepository::delete($this);

        BlueprintDeleted::dispatch($this);

        return true;
    }

    public function ensureField($handle, $fieldConfig, $section = null, $prepend = false)
    {
        return $this->ensureFieldInSection($handle, $fieldConfig, $section, $prepend);
    }

    public function ensureFieldInSection($handle, $config, $section, $prepend = false)
    {
        if (isset($this->ensuredFields[$handle])) {
            return $this;
        }

        $this->ensuredFields[$handle] = compact('handle', 'section', 'prepend', 'config');

        $this->resetFieldsCache();

        return $this;
    }

    public function ensureFieldsInSection($fields, $section, $prepend = false)
    {
        foreach ($fields as $handle => $config) {
            $this->ensureFieldInSection($handle, $config, $section, $prepend);
        }

        return $this;
    }

    public function ensureFieldPrepended($handle, $field, $section = null)
    {
        return $this->ensureField($handle, $field, $section, true);
    }

    public function ensureFieldHasConfig($handle, $config)
    {
        if (! $this->hasField($handle)) {
            return $this;
        }

        foreach ($this->sections()->keys() as $sectionKey) {
            if ($this->hasFieldInSection($handle, $sectionKey)) {
                return $this->ensureFieldInSectionHasConfig($handle, $sectionKey, $config);
            }
        }
    }

    public function removeField($handle, $section = null)
    {
        if (! $this->hasField($handle)) {
            return $this;
        }

        // If a section is specified, only remove from that specific section.
        if ($section) {
            return $this->removeFieldFromSection($handle, $section);
        }

        // Otherwise remove from any section.
        foreach ($this->sections()->keys() as $sectionKey) {
            if ($this->hasFieldInSection($handle, $sectionKey)) {
                return $this->removeFieldFromSection($handle, $sectionKey);
            }
        }
    }

    public function removeSection($handle)
    {
        if (! $this->hasSection($handle)) {
            return $this;
        }

        Arr::pull($this->contents['sections'], $handle);

        return $this->resetFieldsCache();
    }

    public function removeFieldFromSection($handle, $section)
    {
        $fields = collect($this->contents['sections'][$section]['fields'] ?? []);

        // See if field already exists in section.
        if ($this->hasFieldInSection($handle, $section)) {
            $fieldKey = $fields->search(function ($field) use ($handle) {
                return Arr::get($field, 'handle') === $handle;
            });
        } else {
            return $this;
        }

        // Pull it out.
        Arr::pull($this->contents['sections'][$section]['fields'], $fieldKey);

        return $this->resetFieldsCache();
    }

    protected function ensureFieldInSectionHasConfig($handle, $section, $config)
    {
        $fields = collect($this->contents['sections'][$section]['fields'] ?? []);

        // See if field already exists in section.
        if ($this->hasFieldInSection($handle, $section)) {
            $fieldKey = $fields->search(function ($field) use ($handle) {
                return Arr::get($field, 'handle') === $handle;
            });
        } else {
            return $this;
        }

        // Get existing field config.
        $existingConfig = Arr::get($this->contents['sections'][$section]['fields'][$fieldKey], 'field', []);

        // Merge in new field config.
        $this->contents['sections'][$section]['fields'][$fieldKey]['field'] = array_merge($existingConfig, $config);

        return $this->resetFieldsCache();
    }

    public function validateUniqueHandles()
    {
        $fields = $this->fieldsCache ?? new Fields($this->sections()->map->fields()->flatMap->items());

        $handles = $fields->resolveFields()->map->handle();

        if ($field = $handles->duplicates()->first()) {
            throw new DuplicateFieldException($field, $this);
        }
    }

    protected function resetFieldsCache()
    {
        $this->fieldsCache = null;

        Blink::forget($this->contentsBlinkKey());
        Blink::forget($this->fieldsBlinkKey());

        return $this;
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Blueprint::{$method}(...$parameters);
    }

    public function __toString()
    {
        return $this->handle();
    }

    public function augmentedArrayData()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
        ];
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['handle', 'title'];
    }

    protected function normalizeSections()
    {
        if ($fields = Arr::pull($this->contents, 'fields')) {
            $this->contents['sections'] = [
                'main' => ['fields' => $fields],
            ];
        }

        return $this;
    }

    public function addGqlTypes()
    {
        $this->fields()->all()->map->fieldtype()->each->addGqlTypes();
    }

    public function toQueryableValue()
    {
        return $this->handle();
    }
}
