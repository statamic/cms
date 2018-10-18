<?php

namespace Statamic\Events\Data;

use Statamic\API\File;
use Statamic\Contracts\Data\DataEvent;
use Statamic\Data\Data;
use Statamic\Events\Event;

class DataSaved extends Event implements DataEvent
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
    public function __construct(Data $data, $original, $oldPath = null)
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
                return $pathPrefix . $path;
            })
            ->all();
    }
}
