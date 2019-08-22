<?php

namespace Statamic\Widgets;

use Statamic\API\User;
use Statamic\API\Collection as CollectionAPI;

class Collection extends Widget
{
    /**
     * The HTML that should be shown in the widget
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        $collection = $this->config('collection');

        if (! CollectionAPI::handleExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        $collection = CollectionAPI::findByHandle($collection);

        if (! User::current()->can('view', $collection)) {
            return;
        }

        $title = $this->config('title', $collection->title());
        $button = __('New :thing', ['thing' => $collection->entryBlueprint()->title()]);
        $limit = $this->config('limit', 5);

        return view('statamic::widgets.collection', compact('collection', 'title', 'button', 'limit'));
    }
}
