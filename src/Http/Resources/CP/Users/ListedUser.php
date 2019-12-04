<?php

namespace Statamic\Http\Resources\CP\Users;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedUser extends Resource
{
    protected $blueprint;
    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id(),
            'last_login' => optional($this->lastLogin())->diffForHumans() ?? __('Never'),
            $this->merge($this->values(['email' => $this->email()])),
            'super' => $this->isSuper(),
            'edit_url' => $this->editUrl(),
            'editable' => User::current()->can('edit', $this->resource),
            'deleteable' => User::current()->can('delete', $this->resource),
            'actions' => Action::for($this->resource),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;

            $value = $extra[$key] ?? $this->resource->value($key);

            if ($field = $this->blueprint->field($key)) {
                $value = $field->setValue($value)->preProcessIndex()->value();
            }

            return [$key => $value];
        });
    }
}
