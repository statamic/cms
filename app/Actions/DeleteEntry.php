<?php

namespace Statamic\Actions;

use Statamic\API;
use Statamic\API\Site;

class DeleteEntry extends Action
{
    protected static $title = 'Delete';

    protected $dangerous = true;

    public function visibleTo($key, $context)
    {
        if ($key !== 'entries') {
            return false;
        }

        return true;
    }

    public function authorize($key, $context)
    {
        return user()->can("delete {$context['collection']} entries");
    }

    public function run($entries)
    {
        $site = $this->context['site'] ?? Site::current()->handle();

        $entries->each(function ($entry) use ($site) {
            return $entry->in($site)->delete();
        });
    }
}
