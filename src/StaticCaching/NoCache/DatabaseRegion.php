<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Database\Eloquent\Model;

class DatabaseRegion extends Model
{
    protected $table = 'nocache_regions';

    protected $guarded = [];

    protected $primaryKey = 'key';

    public $timestamps = false;

    protected $casts = [
        'key' => 'string',
    ];
}
