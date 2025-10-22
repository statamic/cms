<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class PasskeyModel extends Eloquent
{
    protected $guarded = [];
    protected $table = 'user_passkeys';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'credential' => 'json',
        'last_login' => 'datetime',
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
