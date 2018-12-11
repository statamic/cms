<?php

namespace Statamic\Eloquent\Entries;

use Statamic\API;
use Statamic\Data\Entries\Entry as FileEntry;

class Entry extends FileEntry
{
    public static function fromModel(Model $model)
    {
        return API\Entry::make()
            ->slug($model->slug)
            ->collection($model->collection)
            ->data($model->getAttributes());
    }

    public function date()
    {
        return carbon($this->get('date'));
    }
}
