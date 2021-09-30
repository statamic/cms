<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\FieldsetRepository;
use Statamic\Events\FieldsetDeleted;
use Statamic\Events\FieldsetSaved;
use Statamic\Facades;
use Statamic\Facades\Path;
use Statamic\Support\Str;

class Fieldset
{
    protected $handle;
    protected $contents = [];

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
        return array_get($this->contents, 'title', Str::humanize($this->handle));
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

    public function editUrl()
    {
        return cp_route('fieldsets.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('fieldsets.destroy', $this->handle());
    }

    public function save()
    {
        FieldsetRepository::save($this);

        FieldsetSaved::dispatch($this);

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
