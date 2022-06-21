<?php

namespace Statamic\Http\Resources\CP\Users;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;
use Statamic\Statamic;

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

        if (Statamic::pro()) {
            $columns->put('roles', $columns->get('roles')->fieldtype('relationship')->sortable(false));
            $columns->put('groups', $columns->get('groups')->fieldtype('relationship')->sortable(false));
        } else {
            $columns->forget('roles');
            $columns->forget('groups');
        }

        $columns->forget('avatar');

        $lastLogin = Column::make('last_login')
            ->label(__('Last Login'))
            ->sortable(false)
            ->defaultOrder($columns->max('defaultOrder') + 1);

        $columns->put('last_login', $lastLogin);

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    public function toArray($request)
    {
        $this->setColumns();

        return [
            'data' => $this->collection->each(function ($user) {
                $user
                    ->blueprint($this->blueprint)
                    ->columns($this->requestedColumns());
            }),

            'meta' => [
                'columns' => $this->visibleColumns(),
            ],
        ];
    }
}
