<?php

namespace Statamic\Fieldtypes;

use Illuminate\Http\Resources\Json\JsonResource as Resource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\CP\Column;
use Statamic\Fields\Fieldtype;

abstract class Relationship extends Fieldtype
{
    protected static $preloadable = true;
    protected $component = 'relationship';
    protected $indexComponent = 'relationship';
    protected $itemComponent = 'related-item';
    protected $formComponent;
    protected $categories = ['relationship'];
    protected $relationship = true;
    protected $canEdit = false;
    protected $canCreate = false;
    protected $canSearch = false;
    protected $statusIcons = false;
    protected $taggable = false;
    protected $defaultValue = [];
    protected $formComponentProps = [
        '_' => '_', // forces an object in js
    ];

    protected function configFieldItems(): array
    {
        return [
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'min' => 1,
                'type' => 'integer',
                'width' => 50,
            ],
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                'type' => 'radio',
                'default' => 'default',
                'options' => [
                    'default' => __('Stack Selector'),
                    'select' => __('Select Dropdown'),
                    'typeahead' => __('Typeahead Field'),
                ],
                'width' => 50,
            ],
        ];
    }

    public function preProcess($data)
    {
        return Arr::wrap($data);
    }

    public function preProcessConfig($data)
    {
        $data = $this->preProcess($data);

        return $this->config('max_items') === 1 ? Arr::first($data) : $data;
    }

    public function preProcessIndex($data)
    {
        return $this->getItemsForPreProcessIndex($data)->map(function ($item) {
            return [
                'id' => method_exists($item, 'id') ? $item->id() : $item->handle(),
                'title' => method_exists($item, 'title') ? $item->title() : $item->get('title'),
                'edit_url' => $item->editUrl(),
                'published' => $this->statusIcons ? $item->published() : null,
            ];
        });
    }

    protected function getItemsForPreProcessIndex($values): Collection
    {
        if (! $items = $this->augment($values)) {
            return collect();
        }

        if ($this->config('max_items') === 1) {
            $items = collect([$items]);
        }

        return $items;
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
            $rules[] = 'max:'.$max;
        }

        return $rules;
    }

    public function preload()
    {
        return [
            'data' => $this->getItemData($this->field->value())->all(),
            'columns' => $this->getColumns(),
            'itemDataUrl' => $this->getItemDataUrl(),
            'filtersUrl' => $this->getFiltersUrl(),
            'baseSelectionsUrl' => $this->getBaseSelectionsUrl(),
            'getBaseSelectionsUrlParameters' => $this->getBaseSelectionsUrlParameters(),
            'itemComponent' => $this->getItemComponent(),
            'canEdit' => $this->canEdit(),
            'canCreate' => $this->canCreate(),
            'canSearch' => $this->canSearch(),
            'statusIcons' => $this->statusIcons,
            'creatables' => $this->getCreatables(),
            'formComponent' => $this->getFormComponent(),
            'formComponentProps' => $this->getFormComponentProps(),
            'taggable' => $this->getTaggable(),
        ];
    }

    protected function canCreate()
    {
        if ($this->canCreate === false) {
            return false;
        }

        return $this->config('create', true);
    }

    protected function canEdit()
    {
        if ($this->canEdit === false) {
            return false;
        }

        return $this->config('edit', true);
    }

    protected function canSearch()
    {
        return $this->canSearch;
    }

    protected function getItemComponent()
    {
        return $this->itemComponent;
    }

    protected function getFormComponent()
    {
        return $this->formComponent;
    }

    protected function getFormComponentProps()
    {
        return $this->formComponentProps;
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }

    protected function getItemDataUrl()
    {
        return cp_route('relationship.data');
    }

    protected function getFiltersUrl()
    {
        return cp_route('relationship.filters');
    }

    protected function getBaseSelectionsUrl()
    {
        return cp_route('relationship.index');
    }

    protected function getBaseSelectionsUrlParameters()
    {
        return [];
    }

    public function getSelectionFilters()
    {
        return collect();
    }

    protected function getCreatables()
    {
        return [];
    }

    protected function getCreateItemUrl()
    {
        //
    }

    public function getItemData($values)
    {
        return collect($values)->map(function ($id) {
            return $this->toItemArray($id);
        })->values();
    }

    abstract protected function toItemArray($id);

    protected function invalidItemArray($id)
    {
        return [
            'id' => $id,
            'title' => $id,
            'invalid' => true,
        ];
    }

    public function augment($values)
    {
        $values = $this->collect($values)->map(function ($value) {
            return $this->augmentValue($value);
        })->filter()->values();

        return $this->config('max_items') === 1 ? $values->first() : $values;
    }

    protected function collect($value)
    {
        return collect($value);
    }

    public function shallowAugment($values)
    {
        $values = collect($values)->map(function ($value) {
            return $this->augmentValue($value);
        });

        $values = $values->filter()->map(function ($value) {
            return $this->shallowAugmentValue($value);
        });

        return $this->config('max_items') === 1 ? $values->first() : $values;
    }

    protected function augmentValue($value)
    {
        return $value;
    }

    protected function shallowAugmentValue($value)
    {
        return $value;
    }

    abstract public function getIndexItems($request);

    public function getResourceCollection($request, $items)
    {
        return Resource::collection($items)->additional(['meta' => [
            'columns' => $this->getColumns(),
        ]]);
    }

    public function filterExcludedItems($items, $exclusions)
    {
        return $items->whereNotIn('id', $exclusions)->values();
    }

    public function getSortColumn($request)
    {
        return $request->get('sort');
    }

    public function getSortDirection($request)
    {
        return $request->get('order', 'asc');
    }

    protected function getTaggable()
    {
        return $this->taggable;
    }
}
