<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class PasskeyModel extends Eloquent
{
    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'user_passkeys';

    protected $casts = [
        'data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($table = config('statamic.users.tables.passkeys')) {
            $this->setTable($table);
        }

        $this->setConnection(config('statamic.users.database'));
    }
}
