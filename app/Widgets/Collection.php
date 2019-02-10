<?php

namespace Statamic\Widgets;

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

        $collection = CollectionAPI::whereHandle($collection);

        if (! auth()->user()->can('view', $collection)) {
            return;
        }

        $entries = $collection
            ->queryEntries()
            ->limit($this->config('limit', 5))
            ->get();

        $title = $this->config('title', $collection->title());
        $format = $this->config('date_format', config('statamic.cp.date_format'));
        $button = __('New :thing', ['thing' => $collection->entryBlueprint()->title()]);

        return view('statamic::widgets.collection', compact('collection', 'entries', 'title', 'format', 'button'));
    }
}
