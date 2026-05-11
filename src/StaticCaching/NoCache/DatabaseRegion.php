<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Database\Eloquent\Model;

class DatabaseRegion extends Model
{
    protected $table = 'nocache_regions';

    protected $guarded = [];

    protected $primaryKey = 'key';

    protected function casts(): array
    {
        return [
            'key' => 'string',
        ];
    }

    public function getConnectionName()
    {
        return config('statamic.static_caching.nocache_db_connection') ?: parent::getConnectionName();
    }
}
