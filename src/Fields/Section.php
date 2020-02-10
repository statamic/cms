<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\Field as FieldAPI;
use Statamic\Support\Str;

class Section
{
    protected $handle;
    protected $contents = [];
    protected $extraFields = [];

    public function __construct($handle)
    {
        $this->handle = $handle;
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

    public function fields(): Fields
    {
        $fields = array_get($this->contents, 'fields', []);

        if (! empty($this->extraFields)) {
            foreach ($this->extraFields as $handle => $extra) {
                $new = [
                    'handle' => $handle,
                    'field' => $extra['field']
                ];

                if ($extra['prepend']) {
                    array_unshift($fields, $new);
                } else {
                    $fields[] = $new;
                }
            }
        }

        return new Fields($fields);
    }

    public function extraFields(array $fields)
    {
        $this->extraFields = $fields;

        return $this;
    }

    public function toPublishArray()
    {
        return [
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'handle' => $this->handle,
            'fields' => $this->fields()->toPublishArray()
        ];
    }

    public function display()
    {
        return array_get($this->contents, 'display', Str::humanize($this->handle));
    }

    public function instructions()
    {
        return array_get($this->contents, 'instructions');
    }
}
