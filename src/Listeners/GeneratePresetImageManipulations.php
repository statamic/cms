<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\AssetUploaded;
use Statamic\Facades\Folder;
use Statamic\Facades\Path;
use Statamic\Imaging\PresetGenerator;

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
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(AssetUploaded::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param AssetUploaded $event
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
