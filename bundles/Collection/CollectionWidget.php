<?php

namespace Statamic\Addons\Collection;

use Statamic\API\Collection;
use Statamic\API\Config;
use Statamic\API\Content;
use Statamic\Extend\Widget;

class CollectionWidget extends Widget
{
    public function html()
    {
        $collection = $this->get('collection');

        if (! Collection::handleExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        $collection = Collection::whereHandle($collection);

        $entries = $collection->entries()
            ->removeUnpublished()
            ->limit($this->getInt('limit', 5));

        $title = $this->get('title', $collection->title());

        $format = $this->get('date_format', Config::get('cp.date_format'));

        $button = array_get(
                $collection->fieldset()->contents(),
                'create_title',
                t('create_entry', ['noun' => $collection->fieldset()->title()])
        );

        return $this->view('widget', compact('collection', 'entries', 'title', 'format', 'button'));
    }
}
