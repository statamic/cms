<?php

namespace Statamic\Widgets;

use Statamic\API\Collection as CollectionAPI;
use Statamic\API\Config;
use Statamic\API\Content;
use Statamic\API\User;
use Statamic\Extend\Widget;

class Collection extends Widget
{
    public function html()
    {
        $collection = $this->config('collection');

        if (! Collection::handleExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        // Ensure the collection can be viewed
        if ( ! User::getCurrent()->can("collections:{$collection}:view")) {
            return;
        }

        $collection = Collection::whereHandle($collection);

        $entries = $collection->entries()
            ->removeUnpublished()
            ->limit($this->getInt('limit', 5));

        $title = $this->get('title', $collection->title());

        $format = $this->get('date_format', Config::get('statamic.cp.date_format'));

        $button = array_get(
                $collection->fieldset()->contents(),
                'create_title',
                __('New :thing', ['thing' => $collection->fieldset()->title()])
        );

        return $this->view('widget', compact('collection', 'entries', 'title', 'format', 'button'));
    }
}
