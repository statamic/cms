<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\AssetUploaded;
use Statamic\Imaging\PresetGenerator;

class GeneratePresetImageManipulations implements ShouldQueue
{
    /**
     * @var PresetGenerator
     */
    private $generator;

    /**
     * @param  PresetGenerator  $generator
     */
    public function __construct(PresetGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(AssetUploaded::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param  AssetUploaded  $event
     */
    public function handle($event)
    {
        $asset = $event->asset;

        if (! $asset->isImage()) {
            return;
        }

        $this->generator->generate($asset);
    }
}
