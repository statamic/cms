<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\CP\Column;
use Statamic\Facades\Content;
use Illuminate\Support\Arr;
use Statamic\Facades\Collection;
use Statamic\Fields\Fieldtype;

class Relationship extends Fieldtype
{
    protected static $preloadable = true;
    protected $component = 'relationship';
    protected $indexComponent = 'relationship';
    protected $itemComponent = 'related-item';
    protected $formComponent = 'entry-publish-form';
    protected $categories = ['relationship'];
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $statusIcons = true;
    protected $taggable = false;
    protected $defaultValue = [];

    protected $formComponentProps = [
        'initialActions' => 'actions',
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialLocalizedFields' => 'localizedFields',
        'initialMeta' => 'meta',
        'initialPermalink' => 'permalink',
        'initialLocalizations' => 'localizations',
        'initialHasOrigin' => 'hasOrigin',
        'initialOriginValues' => 'originValues',
        'initialOriginMeta' => 'originMeta',
        'initialSite' => 'locale',
        'initialIsWorkingCopy' => 'hasWorkingCopy',
        'initialIsRoot' => 'isRoot',
        'initialReadOnly' => 'readOnly',
        'revisionsEnabled' => 'revisionsEnabled',
        'breadcrumbs' => 'breadcrumbs',
    ];

    protected $configFields = [
        'max_items' => [
            'type' => 'integer',
            'instructions' => 'Set a maximum number of selectable items',
        ],
        'collections' => [
            'type' => 'collections'
        ],
        'mode' => [
            'type' => 'radio',
            'options' => [
                'default' => 'Default (Drag and drop UI with item selector in a stack)',
                'select' => 'Select (A dropdown field with prepopulated options)',
                'typeahead' => 'Typeahead (A dropdown field with options requested as you type)'
            ]
        ]
    ];

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
        return $this->augment($data)->map(function ($item) use ($data) {
            return [
                'id' => method_exists($item, 'id') ? $item->id() : $item->handle(),
                'title' => method_exists($item, 'title') ? $item->title() : $item->get('title'),
                'edit_url' => $item->editUrl(),
                'published' => $this->statusIcons ? $item->published() : null,
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
        return [
            'data' => $this->getItemData($this->field->value())->all(),
            'columns' => $this->getColumns(),
            'itemDataUrl' => $this->getItemDataUrl(),
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
            Column::make('collection'),
            Column::make('url')->label('URL'),
        ];
    }

    protected function getItemDataUrl()
    {
        return cp_route('relationship.data');
    }

    protected function getBaseSelectionsUrl()
    {
        return cp_route('relationship.index');
    }

    protected function getBaseSelectionsUrlParameters()
    {
        return [
            'collections' => $this->config('collections'),
        ];
    }

    protected function getCreatables()
    {
        if ($url = $this->getCreateItemUrl()) {
            return [['url' => $url]];
        }

        $collections = $this->config('collections', Collection::handles());

        return collect($collections)->map(function ($collection) {
            $collection = Collection::findByHandle($collection);

            return [
                'title' => $collection->title(),
                'url' => $collection->createEntryUrl(Site::selected()->handle()),
            ];
        })->all();
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

    public function augment($values)
    {
        return collect($values)->map(function ($value) {
            return $this->augmentValue($value);
        });
    }

    protected function augmentValue($value)
    {
        if ($entry = Entry::find($value)) {
            return $entry;
        }
    }

    public function getIndexItems($request)
    {
        return $this->getIndexQuery($request)
            ->orderBy($this->getSortColumn($request), $this->getSortDirection($request))
            ->paginate();
    }

    public function getSortColumn($request)
    {
        return $request->get('sort', 'title');
    }

    public function getSortDirection($request)
    {
        return $request->get('order', 'asc');
    }

    protected function getIndexQuery($request)
    {
        $query = Entry::query();

        if ($collections = $request->collections) {
            $query->whereIn('collection', $collections);
        }

        if ($search = $request->search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($site = $request->site) {
            $query->where('site', $site);
        }

        return $query;
    }

    protected function getTaggable()
    {
        return $this->taggable;
    }
}
