<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Entry;
use Illuminate\Support\Arr;
use Statamic\Fields\Fieldtype;

class Relationship extends Fieldtype
{
    protected $categories = ['relationship'];

    protected $configFields = [
        'max_items' => ['type' => 'integer'],
        'collections' => ['type' => 'collections'],
    ];

    public function preProcess($data)
    {
        return Arr::wrap($data);
    }

    public function process($data)
    {
        if ($data === null || $data === []) {
            return null;
        }

        return $data;
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($max = $this->config('max_items')) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }

    public function preload()
    {
        $data = $this->getItemData($this->field->value())->all();

        return compact('data');
    }

    public function getItemData($values)
    {
        return collect($values)->map(function ($id) {
            return $this->toItemArray($id);
        });
    }

    protected function toItemArray($id)
    {
        if ($entry = Entry::find($id)) {
            return $entry->toArray();
        }

        return $this->invalidItemArray($id);
    }

    protected function invalidItemArray($id)
    {
        return [
            'id' => $id,
            'title' => $id,
            'invalid' => true
        ];
    }
}
