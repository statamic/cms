<?php

namespace Statamic\Listeners;

use Statamic\API\Path;
use Statamic\API\Folder;
use Statamic\Imaging\PresetGenerator;
use Statamic\Events\Data\AssetUploaded;
use Statamic\Events\Data\AssetReplaced;
use Illuminate\Contracts\Queue\ShouldQueue;

class GeneratePresetImageManipulations implements ShouldQueue
{
    /**
     * @var PresetGenerator
     */
    private $generator;

    /**
     * @param PresetGenerator $generator
     */
    public function __construct(PresetGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Register the listeners for the subscriber
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(AssetUploaded::class, self::class.'@handle');
        $events->listen(AssetReplaced::class, self::class.'@handle');
    }

    /**
     * Handle the events
     *
     * @param AssetUploaded|AssetReplaced $event
     */
    public function handle($event)
    {
        $asset = $event->asset;

        if (! $asset->isImage()) {
            return;
        }

        $folder = Path::tidy('local/cache/glide/containers/'.$asset->containerId().'/'.$asset->path());

        if (Folder::exists($folder)) {
            Folder::delete($folder);
        }

        $this->generator->generate($asset);
    }
}
