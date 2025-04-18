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

    /**
     * Get the database connection for the model.
     */
    public function getConnectionName(): ?string
    {
        // Use the connection from config, or fall back to parent (default connection)
        return config('statamic.static_caching.nocache_db_connection') ?: parent::getConnectionName();
    }
}
