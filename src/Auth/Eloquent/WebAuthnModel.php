<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class WebAuthnModel extends Eloquent
{
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'credential' => 'json',
        'last_login' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('statamic.users.tables.webauthn', 'webauthn'));

        $this->setConnection(config('statamic.users.database'));
    }
}
