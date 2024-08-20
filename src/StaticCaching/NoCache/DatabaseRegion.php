<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Database\Eloquent\Model;

class DatabaseRegion extends Model
{
    protected $table = 'static_cache_regions';

    protected $guarded = [];
}
