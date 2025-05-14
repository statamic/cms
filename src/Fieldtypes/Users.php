<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\CP\Column;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\Search;
use Statamic\Facades\User;
use Statamic\GraphQL\Types\UserType;
use Statamic\Query\OrderedQueryBuilder;
use Statamic\Query\Scopes\Filter;
use Statamic\Query\Scopes\Filters\Fields\User as UserFilter;
use Statamic\Search\Result;
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
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'type' => 'integer',
                        'min' => 1,
                    ],
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.any.config.mode'),
                        'type' => 'radio',
                        'options' => [
                            'default' => __('Stack Selector'),
                            'select' => __('Select Dropdown'),
                            'typeahead' => __('Typeahead Field'),
                        ],
                        'default' => 'select',
                    ],
                    'default' => [
                        'display' => __('Default'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'users',
                    ],
                    'query_scopes' => [
                        'display' => __('Query Scopes'),
                        'instructions' => __('statamic::fieldtypes.users.config.query_scopes'),
                        'type' => 'taggable',
                        'options' => Scope::all()
                            ->reject(fn ($scope) => $scope instanceof Filter)
                            ->map->handle()
                            ->values()
                            ->all(),
                    ],
                ],
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
                'editable' => User::current()->can('edit', $user),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        $query = User::query();

        if ($search = $request->search) {
            if (Search::indexes()->has('users')) {
                $query = Search::index('users')->ensureExists()->search($search);
            } else {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('email', 'like', '%'.$search.'%')
                        ->when(User::blueprint()->hasField('first_name'), function ($query) use ($search) {
                            foreach (explode(' ', $search) as $word) {
                                $query
                                    ->orWhere('first_name', 'like', '%'.$word.'%')
                                    ->orWhere('last_name', 'like', '%'.$word.'%');
                            }
                        }, function ($query) use ($search) {
                            $query->orWhere('name', 'like', '%'.$search.'%');
                        });
                });
            }
        }

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
        }

        $this->applyIndexQueryScopes($query, $request->all());

        $userFields = function ($user) {
            if ($user instanceof Result) {
                $user = $user->getSearchable();
            }

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
        $single = $this->config('max_items') === 1;

        $ids = Arr::wrap($values);

        $query = (new OrderedQueryBuilder(User::query(), $ids))->whereIn('id', $ids);

        return $single && ! config('statamic.system.always_augment_to_query', false)
            ? $query->first()
            : $query;
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
