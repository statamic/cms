<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Database\Eloquent\Model;

class PageModel extends Model
{
    public $timestamps = false;

    protected $table = 'static_cache';

    protected $guarded = [];

    protected function casts()
    {
        return [
            'headers' => 'array',
        ];
    }
}
