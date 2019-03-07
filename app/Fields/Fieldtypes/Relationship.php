<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Content;
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

    public function preProcessIndex($data)
    {
        return $this->augment($data)->map(function ($item) {
            return [
                'id' => $item->id(),
                'title' => $item->get('title'),
                'edit_url' => $item->editUrl(),
                'published' => $item->published(),
            ];
        });
    }

    public function process($data)
    {
        if ($data === null || $data === []) {
            return null;
        }

        if ($this->field->get('max_items') === 1) {
            return $data[0];
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

    public function getItemData($values, $site = null)
    {
        $site = $site ?? Site::selected()->handle();

        return collect($values)->map(function ($id) use ($site) {
            return $this->toItemArray($id, $site);
        })->values();
    }

    protected function toItemArray($id, $site)
    {
        if ($entry = Entry::find($id)) {
            return $entry->in($site)->toArray();
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

    public function augment($values)
    {
        return collect($values)->map(function ($value) {
            return Content::find($value);
        });
    }
}
