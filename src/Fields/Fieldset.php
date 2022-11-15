<?php

namespace Statamic\Fields;

use Statamic\Events\FieldsetCreated;
use Statamic\Events\FieldsetDeleted;
use Statamic\Events\FieldsetSaved;
use Statamic\Events\FieldsetSaving;
use Statamic\Facades;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Facades\Path;
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
