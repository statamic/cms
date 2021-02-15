<?php

namespace Statamic\Support\Traits;

use Statamic\Events\EntrySaved;
use Statamic\Events\FormSubmitted;

trait EventHelper
{
    public function hasHandle($name, $event): bool
    {
        if ($event instanceof EntrySaved) {
            // TODO: Check against all existing Entry events
            return $event->entry->handle() === $name;
        }

        if ($event instanceof FormSubmitted) {
            // TODO: Check against all existing Form events
            return $event->submission->form->handle() === $name;
        }

        // TODO: Check against other Event types

        return false;
    }

    public function isEntry($event): bool
    {
        if ($event instanceof EntrySaved) {
            return true;
        }

        // TODO: Check against all registration form events

        return false;
    }

    public function isForm($event): bool
    {
        if ($event instanceof FormSubmitted) {
            return true;
        }

        // TODO: Check against all registration form events

        return false;
    }
}
