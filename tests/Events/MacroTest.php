<?php

namespace Tests\Events;

use Illuminate\Support\Facades\Event;
use Statamic\Events\Event as StatamicEvent;
use Statamic\Events\Subscriber;
use Tests\TestCase;

class MacroTest extends TestCase
{
    /** @test */
    public function it_can_forget_a_listener_using_string_notation()
    {
        Event::listen(JokeSaved::class, 'Listener@handle');

        $this->assertRegisteredListenersForEvent(JokeSaved::class, [
            'Listener@handle',
        ]);

        Event::forgetListener(JokeSaved::class, 'Listener@handle');

        $this->assertNoRegisteredListenersForEvent(JokeSaved::class);
    }

    /** @test */
    public function it_can_forget_a_listener_using_array_notation()
    {
        Event::listen(JokeSaved::class, ['Listener', 'handle']);

        $this->assertRegisteredListenersForEvent(JokeSaved::class, [
            ['Listener', 'handle'],
        ]);

        Event::forgetListener(JokeSaved::class, ['Listener', 'handle']);

        $this->assertNoRegisteredListenersForEvent(JokeSaved::class);
    }

    /** @test */
    public function forgetting_a_listener_doesnt_affect_other_events_or_listeners()
    {
        Event::listen(JokeSaved::class, 'SubscriberOne@handle');
        Event::listen(JokeSaved::class, 'SubscriberTwo@handle');
        Event::listen(JokeDeleted::class, 'SubscriberOne@handle');

        $this->assertRegisteredListenersForEvent(JokeSaved::class, [
            'SubscriberOne@handle',
            'SubscriberTwo@handle',
        ]);

        $this->assertRegisteredListenersForEvent(JokeDeleted::class, [
            'SubscriberOne@handle',
        ]);

        Event::forgetListener(JokeSaved::class, 'SubscriberOne@handle');

        $this->assertRegisteredListenersForEvent(JokeSaved::class, [
            'SubscriberTwo@handle',
        ]);

        $this->assertRegisteredListenersForEvent(JokeDeleted::class, [
            'SubscriberOne@handle',
        ]);
    }

    private function assertRegisteredListenersForEvent($event, $listeners)
    {
        $this->assertEquals($listeners, $this->getRegisteredListenersForEvent($event));
    }

    private function assertNoRegisteredListenersForEvent($event)
    {
        $this->assertCount(0, $this->getRegisteredListenersForEvent($event));
    }

    private function getRegisteredListenersForEvent($event)
    {
        $dispatcher = app('events');

        // This `getRawListeners()` method doesn't exist in older versions of Laravel.
        // We should be able remove this when we drop support for Laravel 8.x.
        if (! method_exists($dispatcher, 'getRawListeners')) {
            Event::macro('getRawListeners', function () {
                return $this->listeners;
            });
        }

        return collect($dispatcher->getRawListeners()[$event])
            ->map(function ($listener) {
                return Subscriber::normalizeRegisteredListener($listener);
            })
            ->values()
            ->all();
    }
}

class JokeSaved extends StatamicEvent
{
    //
}

class JokeDeleted extends StatamicEvent
{
    //
}
