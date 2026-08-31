<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RoleModel extends Eloquent
{
    protected $guarded = [];

    protected $table = 'roles';

    protected function casts(): array
    {
        return [
            'permissions' => 'json',
            'preferences' => 'json',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($table = config('statamic.users.tables.roles')) {
            $this->setTable($table);
        }

        $this->setConnection(config('statamic.users.database'));
    }
}
