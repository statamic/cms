<?php

namespace Statamic\Http\Resources\CP\Users;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\Facades\UserGroup;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;
use Statamic\Statamic;
use Statamic\Support\Arr;

class Users extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ListedUser::class;
    protected $blueprint;
    protected $columns;
    protected $columnPreferenceKey;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columnPreferenceKey($key)
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    private function setColumns()
    {
        $columns = $this->blueprint->columns();

        // Ensure email column is always first on listing
        $columns->prepend($columns->pull('email')->defaultOrder(0), 'email');

        // Forget avatar, because it is rendered within the email column
        $columns->forget('avatar');

        // Configure roles and groups columns
        if (Statamic::pro()) {
            $columns->put('roles', $columns->get('roles')->fieldtype('relationship')->sortable(false));
            $columns->put('groups', $columns->get('groups')->fieldtype('relationship')->sortable(false));
        } else {
            $columns->forget('roles');
            $columns->forget('groups');
        }

        // Append last login column
        $columns->put('last_login',
            Column::make('last_login')
                ->label(__('Last Login'))
                ->sortable(false)
                ->defaultOrder($columns->max('defaultOrder') + 1)
        );

        // Normalize default visibility for when user blueprint file exists in app
        $columns->transform(function ($column) {
            return $this->normalizeDefaultVisibilityOnColumn($column);
        });

        // Wire up column preferences
        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    private function normalizeDefaultVisibilityOnColumn($column)
    {
        $defaultVisible = ['email', 'name', 'first_name', 'last_name', 'roles', 'groups'];

        if (! in_array($column->field(), $defaultVisible)) {
            return $column;
        }

        // Ensure default visibility if `listable` is not set on blueprint
        if (Arr::get($this->blueprint->field($column->field())->config(), 'listable') === null) {
            $column->defaultVisibility(true)->visible(true);
        }

        // Only show groups if groups actually exist
        if ($column->field() === 'groups' && UserGroup::all()->count() === 0) {
            $column->defaultVisibility(false)->visible(false);
        }

        return $column;
    }

    public function toArray($request)
    {
        $this->setColumns();

        return $this->collection->each(function ($user) {
            $user
                ->blueprint($this->blueprint)
                ->columns($this->requestedColumns());
        });
    }

    public function with($request)
    {
        return [
            'meta' => [
                'columns' => $this->visibleColumns(),
            ],
        ];
    }
}
