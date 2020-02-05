<?php

namespace Statamic\Fields;

use Statamic\Facades;
use Statamic\Support\Str;
use Facades\Statamic\Fields\FieldsetRepository;

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

    public function setContents(array $contents)
    {
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

    public function fields()
    {
        $fields = array_get($this->contents, 'fields', []);

        return collect($fields)->map(function ($config, $handle) {
            return new Field($handle, $config);
        });
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

        return $this;
    }

    public function delete()
    {
        FieldsetRepository::delete($this);

        return true;
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Fieldset::{$method}(...$parameters);
    }
}
