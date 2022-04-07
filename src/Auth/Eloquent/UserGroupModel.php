<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserGroupModel extends Eloquent
{
    protected $guarded = [];

    protected $table = 'groups';

    protected $casts = [
        'roles' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($table = config('statamic.users.groups', '')) {
            $this->setTable($table);
        }

        $this->setConnection(config('statamic.users.database'));
    }
}
