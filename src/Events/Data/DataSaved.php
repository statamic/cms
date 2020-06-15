<?php

namespace Statamic\Events\Data;

use Statamic\Contracts\Data\DataSavedEvent;
use Statamic\Data\Data;
use Statamic\Events\Event;
use Statamic\Facades\File;

class DataSaved extends Event implements DataSavedEvent
{
    /**
     * @var Data
     */
    public $data;

    /**
     * @var array
     */
    public $original;

    /**
     * @var string
     */
    public $oldPath;

    /**
     * @param Data $data
     * @param array $original
     * @param string|null $ooldPath
     */
    public function __construct($data, $original, $oldPath = null)
    {
        $this->data = $data;
        $this->original = $original;
        $this->oldPath = $oldPath;
    }

    /**
     * Get contextual data related to event.
     *
     * @return array
     */
    public function contextualData()
    {
        return $this->data->toArray();
    }

    /**
     * Get paths affected by event.
     *
     * @return array
     */
    public function affectedPaths()
    {
        $disk = isset($this->disk) ? $this->disk : 'content';
        $pathPrefix = File::disk($disk)->filesystem()->getAdapter()->getPathPrefix();

        return collect([$this->oldPath, $this->data->path()])
            ->filter()
            ->map(function ($path) use ($pathPrefix) {
                return $pathPrefix.$path;
            })
            ->all();
    }
}
