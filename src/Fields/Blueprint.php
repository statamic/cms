<?php

namespace Statamic\Fields;

use ArrayAccess;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Statamic\CommandPalette\Category;
use Statamic\CommandPalette\Link;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\QueryableValue;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\BlueprintCreated;
use Statamic\Events\BlueprintCreating;
use Statamic\Events\BlueprintDeleted;
use Statamic\Events\BlueprintDeleting;
use Statamic\Events\BlueprintReset;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\BlueprintSaving;
use Statamic\Exceptions\DuplicateFieldException;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Blueprint implements Arrayable, ArrayAccess, Augmentable, QueryableValue
{
    use ExistsAsFile, HasAugmentedData;

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
    private $lastBlueprintHandle = null;

    private ?Columns $columns = null;

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

    public function renderableNamespace(): string
    {
        return str_replace('.', ' ', Str::humanize($this->namespace));
    }

    public function fullyQualifiedHandle(): string
    {
        $handle = $this->handle();

        if ($this->namespace()) {
            $handle = $this->isNamespaced()
                ? $this->namespace().'::'.$handle
                : $this->namespace().'.'.$handle;
        }

        return $handle;
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
        $namespace = str_replace('.', '/', (string) $this->namespace());

        if ($this->isNamespaced()) {
            $namespace = 'vendor/'.$namespace;
        }

        return Path::tidy(vsprintf('%s/%s/%s.yaml', [
            Facades\Blueprint::directory(),
            $namespace,
            $this->handle(),
        ]));
    }

    public function setContents(array $contents)
    {
        $this->contents = $contents;

        return $this
            ->normalizeTabs()
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

        $contents['tabs'] = $contents['tabs'] ?? [
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
        $tab = $ensured['tab'] ?? array_keys($contents['tabs'])[0] ?? 'main';
        $prepend = $ensured['prepend'];
        $addedTab = false;

        // If ensuring into a new tab, make sure it exists.
        if (! isset($contents['tabs'][$tab])) {
            $addedTab = true;
            $contents['tabs'][$tab] = ['sections' => [['fields' => []]]];
        }

        $tabs = collect($contents['tabs'] ?? []);

        // Get all the fields, and mark which tab they're in.
        $allFields = $tabs->flatMap(function ($tab, $tabHandle) {
            return collect($tab['sections'] ?? [])->flatMap(function ($section, $sectionIndex) use ($tabHandle) {
                return collect($section['fields'] ?? [])->keyBy(function ($field) {
                    return (isset($field['import'])) ? 'import:'.($field['prefix'] ?? null).$field['import'] : $field['handle'];
                })->map(function ($field) use ($tabHandle, $sectionIndex) {
                    $field['tab'] = $tabHandle;
                    $field['section'] = $sectionIndex;

                    return $field;
                });
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
            // If we added the tab early but it turns out this field already existed, remove the tab so it
            // it doesn't end up in the contents.
            if ($addedTab) {
                $tabs->forget($tab);
                unset($contents['tabs'][$tab]);
            }

            // Since it already exists, maintain the position in that tab.
            $tab = $existingField['tab'];

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

        $targetSectionIndex = $existingField['section']
            ?? ($prepend ? 0 : count($contents['tabs'][$tab]['sections'] ?? []) - 1);

        $fields = collect($tabs[$tab]['sections'][$targetSectionIndex]['fields'] ?? [])->keyBy(function ($field) {
            return (isset($field['import'])) ? 'import:'.($field['prefix'] ?? null).$field['import'] : $field['handle'];
        });

        if (! $exists) {
            if ($importedField = $importedFields->get($handle)) {
                $importKey = 'import:'.$importedField['partial'];
                $field = $allFields->get($importKey);
                $tab = $field['tab'];
                $fields = collect($tabs[$tab]['sections'][$targetSectionIndex]['fields'])->keyBy(function ($field) {
                    return (isset($field['import'])) ? 'import:'.($field['prefix'] ?? null).$field['import'] : $field['handle'];
                });
                $importedConfig = $importedField['field']->config();
                $config = array_merge($config, $importedConfig);
                $config = Arr::except($config, array_keys($importedConfig));
                $field['config'][$handle] = $config;
                unset($field['tab']);
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

        $contents['tabs'][$tab]['sections'][$targetSectionIndex]['fields'] = $fields->values()->all();

        return $contents;
    }

    public function fileData()
    {
        return $this->contents();
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        $this->resetBlueprintCache()->resetFieldsCache();

        return $this;
    }

    public function parent()
    {
        return $this->parent;
    }

    public function tabs(): Collection
    {
        return collect(Arr::get($this->contents(), 'tabs', []))->map(function ($contents, $handle) {
            return (new Tab($handle))->setContents($contents);
        });
    }

    public function fields(): Fields
    {
        if ($this->fieldsCache) {
            return $this->fieldsCache;
        }

        $fn = function () {
            $this->validateUniqueHandles();

            return new Fields($this->tabs()->map->fields()->flatMap->items());
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

    public function hasTab($tab)
    {
        return $this->tabs()->has($tab);
    }

    public function hasFieldInTab($field, $tab)
    {
        if ($tab = $this->tabs()->get($tab)) {
            return $tab->fields()->has($field);
        }

        return false;
    }

    public function field($field)
    {
        return $this->fields()->get($field);
    }

    public function columns()
    {
        if ($this->columns) {
            return $this->columns;
        }

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

        return $this->columns = new Columns($columns);
    }

    public function isEmpty(): bool
    {
        return $this->fields()->all()->isEmpty();
    }

    public function title()
    {
        return Arr::get($this->contents, 'title', Str::humanize(Str::of($this->handle)->after('::')->afterLast('.')));
    }

    public function isNamespaced(): bool
    {
        return Facades\Blueprint::getAdditionalNamespaces()->has($this->namespace);
    }

    public function isDeletable()
    {
        return ! $this->isNamespaced();
    }

    public function isResettable()
    {
        return $this->isNamespaced()
            && File::exists($this->path());
    }

    public function toPublishArray()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
            'tabs' => $this->tabs()->map->toPublishArray()->values()->all(),
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
            if ($isNew && BlueprintCreating::dispatch($this) === false) {
                return false;
            }

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

    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents && BlueprintDeleting::dispatch($this) === false) {
            return false;
        }

        BlueprintRepository::delete($this);

        if ($withEvents) {
            BlueprintDeleted::dispatch($this);
        }

        return true;
    }

    public function reset()
    {
        BlueprintRepository::reset($this);

        BlueprintReset::dispatch($this);

        return true;
    }

    public function ensureField($handle, $fieldConfig, $tab = null, $prepend = false)
    {
        return $this->ensureFieldInTab($handle, $fieldConfig, $tab, $prepend);
    }

    public function ensureFieldInTab($handle, $config, $tab, $prepend = false)
    {
        if (isset($this->ensuredFields[$handle])) {
            return $this;
        }

        $this->ensuredFields[$handle] = compact('handle', 'tab', 'prepend', 'config');

        $this->resetBlueprintCache()->resetFieldsCache();

        return $this;
    }

    public function ensureFieldsInTab($fields, $tab, $prepend = false)
    {
        foreach ($fields as $handle => $config) {
            $this->ensureFieldInTab($handle, $config, $tab, $prepend);
        }

        return $this;
    }

    public function ensureFieldPrepended($handle, $field, $tab = null)
    {
        return $this->ensureField($handle, $field, $tab, true);
    }

    public function ensureFieldHasConfig($handle, $config)
    {
        if (! $this->hasField($handle)) {
            return $this;
        }

        foreach ($this->tabs()->keys() as $tabKey) {
            if ($this->hasFieldInTab($handle, $tabKey)) {
                return $this->ensureFieldInTabHasConfig($handle, $tabKey, $config);
            }
        }
    }

    public function removeField($handle, $tab = null)
    {
        if (! $this->hasField($handle)) {
            return $this;
        }

        // If a tab is specified, only remove from that specific tab.
        if ($tab) {
            return $this->removeFieldFromTab($handle, $tab);
        }

        // Otherwise remove from any tab.
        foreach ($this->tabs()->keys() as $tabKey) {
            if ($this->hasFieldInTab($handle, $tabKey)) {
                return $this->removeFieldFromTab($handle, $tabKey);
            }
        }
    }

    public function removeTab($handle)
    {
        if (! $this->hasTab($handle)) {
            return $this;
        }

        Arr::pull($this->contents['tabs'], $handle);

        return $this->resetBlueprintCache()->resetFieldsCache();
    }

    public function removeFieldFromTab($handle, $tab)
    {
        $fields = $this->getTabFields($tab);

        // See if field already exists in tab.
        if (! $this->hasFieldInTab($handle, $tab)) {
            return $this;
        }

        $fieldKey = $fields[$handle]['fieldIndex'];
        $sectionIndex = $fields[$handle]['sectionIndex'];

        // Pull it out.
        Arr::pull($this->contents['tabs'][$tab]['sections'][$sectionIndex]['fields'], $fieldKey);

        return $this->resetBlueprintCache()->resetFieldsCache();
    }

    private function getTabFields($tab)
    {
        return collect($this->contents['tabs'][$tab]['sections'])->flatMap(function ($section, $sectionIndex) {
            return collect($section['fields'] ?? [])->map(function ($field, $fieldIndex) use ($sectionIndex) {
                return $field + ['fieldIndex' => $fieldIndex, 'sectionIndex' => $sectionIndex];
            });
        })->keyBy('handle');
    }

    protected function ensureFieldInTabHasConfig($handle, $tab, $config)
    {
        $fields = $this->getTabFields($tab);

        // See if field already exists in tab.
        if (! $this->hasFieldInTab($handle, $tab)) {
            return $this;
        }

        // If field is deferred as an ensured field, we'll need to update it instead
        if (! isset($fields[$handle]) && isset($this->ensuredFields[$handle])) {
            return $this->ensureEnsuredFieldHasConfig($handle, $config);
        }

        $fieldKey = $fields[$handle]['fieldIndex'];
        $sectionKey = $fields[$handle]['sectionIndex'];

        $field = $this->contents['tabs'][$tab]['sections'][$sectionKey]['fields'][$fieldKey];

        $isImportedField = Arr::has($field, 'config');

        if ($isImportedField) {
            $existingConfig = Arr::get($field, 'config', []);
            $this->contents['tabs'][$tab]['sections'][$sectionKey]['fields'][$fieldKey]['config'] = array_merge($existingConfig, $config);
        } else {
            $existingConfig = Arr::get($field, 'field', []);
            $this->contents['tabs'][$tab]['sections'][$sectionKey]['fields'][$fieldKey]['field'] = array_merge($existingConfig, $config);
        }

        return $this->resetBlueprintCache()->resetFieldsCache();
    }

    private function ensureEnsuredFieldHasConfig($handle, $config)
    {
        if (! isset($this->ensuredFields[$handle])) {
            return $this;
        }

        $existingConfig = Arr::get($this->ensuredFields[$handle], 'config', []);

        $this->ensuredFields[$handle]['config'] = array_merge($existingConfig, $config);

        return $this->resetBlueprintCache()->resetFieldsCache();
    }

    public function validateUniqueHandles()
    {
        $fields = $this->fieldsCache ?? new Fields($this->tabs()->map->fields()->flatMap->items());

        $handles = $fields->resolveFields()->map->handle();

        if ($field = $handles->duplicates()->first()) {
            throw new DuplicateFieldException($field, $this);
        }
    }

    protected function resetBlueprintCache()
    {
        $this->lastBlueprintHandle = null;

        return $this;
    }

    protected function resetFieldsCache()
    {
        if ($this->parent) {
            $blueprint = (fn () => property_exists($this, 'blueprint') ? $this->blueprint : null)->call($this->parent);

            if ($blueprint && $blueprint === $this->lastBlueprintHandle) {
                return $this;
            }

            $this->lastBlueprintHandle = $blueprint;
        }

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

    protected function normalizeTabs()
    {
        if ($fields = Arr::pull($this->contents, 'fields')) {
            $this->contents['tabs'] = [
                'main' => [
                    'sections' => [
                        [
                            'fields' => $fields,
                        ],
                    ],
                ],
            ];
        }

        if ($sections = Arr::pull($this->contents, 'sections')) {
            $this->contents['tabs'] = collect($sections)->map(function ($section) {
                return array_filter([
                    'display' => $section['display'] ?? null,
                    'sections' => [
                        [
                            'fields' => $section['fields'],
                        ],
                    ],
                ]);
            })->all();
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

    public function editAdditionalBlueprintUrl()
    {
        return cp_route('blueprints.additional.edit', [$this->namespace(), $this->handle()]);
    }

    public function resetAdditionalBlueprintUrl()
    {
        return cp_route('blueprints.additional.reset', [$this->namespace(), $this->handle()]);
    }

    public function writeFile($path = null)
    {
        File::put($path ?? $this->buildPath(), $this->fileContents());
    }

    public function commandPaletteLink(string $type, string $url): Link
    {
        $text = __('Blueprints').' » '.__($type).' » '.__($this->title());

        return (new Link($text, Category::Fields))
            ->url($url)
            ->icon('blueprints');
    }
}
