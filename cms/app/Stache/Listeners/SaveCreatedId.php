<?php

namespace Statamic\Stache\Listeners;

use Statamic\Events\DataIdCreated;

class SaveCreatedId
{
    /**
     * Handle the event.
     *
     * @param DataIdCreated $event
     * @return void
     */
    public function handle(DataIdCreated $event)
    {
        $event->data->save();
    }
}