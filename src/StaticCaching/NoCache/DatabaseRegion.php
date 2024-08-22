<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Database\Eloquent\Model;

class DatabaseRegion extends Model
{
    protected $table = 'nocache_regions';

    protected $guarded = [];

    protected $primaryKey = 'key';

    protected $casts = [
        'key' => 'string',
    ];
}
