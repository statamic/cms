<?php

namespace Statamic\Eloquent\Entries;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    protected $guarded = [];
    protected $table = 'entries';
    protected $casts = [
        'date' => 'date',
        'data' => 'json',
        'published' => 'bool',
    ];

    public function origin()
    {
        return $this->belongsTo(self::class);
    }
}
