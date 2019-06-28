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
        return $key === 'entries';
    }

    public function authorize($entry)
    {
        return user()->can('delete', $entry);
    }

    public function run($entries)
    {
        $site = $this->context['site'] ?? Site::current()->handle();

        $entries->each(function ($entry) use ($site) {
            return $entry->in($site)->delete();
        });
    }
}
