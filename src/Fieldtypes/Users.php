<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\CP\Column;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;
use Statamic\GraphQL\Types\UserType;
use Statamic\Query\OrderedQueryBuilder;
use Statamic\Query\Scopes\Filters\Fields\User as UserFilter;
use Statamic\Support\Arr;

class Users extends Relationship
{
    protected $statusIcons = false;
    protected $formComponent = 'user-publish-form';
    protected $canEdit = true;

    protected $formComponentProps = [
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'actions' => 'actions',
        'canEditPassword' => 'canEditPassword',
    ];

    protected function configFieldItems(): array
    {
        return [
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'type' => 'integer',
                'min' => 1,
            ],
            'mode' => [
                'display' => __('Mode'),
                'type' => 'radio',
                'options' => [
                    'default' => __('Stack Selector'),
                    'select' => __('Select Dropdown'),
                    'typeahead' => __('Typeahead Field'),
                ],
                'default' => 'select',
                'width' => 50,
            ],
        ];
    }

    public function preProcess($data)
    {
        if ($data === 'current') {
            $data = User::current()->id();
        }

        return parent::preProcess($data);
    }

    protected function toItemArray($id, $site = null)
    {
        if ($user = User::find($id)) {
            return [
                'title' => $user->name(),
                'id' => $id,
                'edit_url' => $user->editUrl(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        $query = User::query();

        if ($search = $request->search) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
        }

        $query->orderBy('name');

        $userFields = function ($user) {
            return [
                'id' => $user->id(),
                'title' => $user->name(),
                'email' => $user->email(),
            ];
        };

        if ($request->boolean('paginate', true)) {
            $users = $query->paginate();

            $users->getCollection()->transform($userFields);

            return $users;
        }

        return $query->get()->map($userFields);
    }

    protected function getColumns()
    {
        return [
            Column::make('title')->label('Name'),
            Column::make('email'),
        ];
    }

    public function preProcessIndex($data)
    {
        return $this->getItemsForPreProcessIndex($data)->map(function ($user) {
            return [
                'id' => $user->id(),
                'title' => $user->name(),
                'edit_url' => $user->editUrl(),
                'published' => null,
            ];
        })->filter()->values();
    }

    protected function getItemsForPreProcessIndex($values): Collection
    {
        if (! $augmented = $this->augment($values)) {
            return collect();
        }

        return $this->config('max_items') === 1 ? collect([$augmented]) : $augmented->get();
    }

    public function augment($values)
    {
        $ids = Arr::wrap($values);

        $query = (new OrderedQueryBuilder(User::query(), $ids))->whereIn('id', $ids);

        return $this->config('max_items') === 1 ? $query->first() : $query;
    }

    public function shallowAugment($values)
    {
        $items = $this->augment($values);

        if ($this->config('max_items') === 1) {
            $items = collect([$items]);
        } else {
            $items = $items->get();
        }

        $items = $items->filter()->map(function ($item) {
            return $item->toShallowAugmentedCollection();
        })->collect();

        return $this->config('max_items') === 1 ? $items->first() : $items;
    }

    protected function getCreateItemUrl()
    {
        return cp_route('users.create');
    }

    public function toGqlType()
    {
        $type = GraphQL::type(UserType::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }

    public function filter()
    {
        return new UserFilter($this);
    }
}
