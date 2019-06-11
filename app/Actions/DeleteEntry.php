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

        // TODO: Should this be?
        // $collection = Collection::findByHandle($context['collection']);
        // return user()->can('delete', [Entry::class, $collection]);
    }

    public function run($entries)
    {
        $site = $this->context['site'] ?? Site::current()->handle();

        $entries->each(function ($entry) use ($site) {
            return $entry->in($site)->delete();
        });
    }
}
