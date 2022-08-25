<?php

namespace Tests\Events;

use Illuminate\Support\Facades\Event;
use Statamic\Events\Event as StatamicEvent;
use Tests\TestCase;

class MacroTest extends TestCase
{
    /** @test */
    public function it_can_forget_a_listener_using_string_notation()
    {
        Event::listen(PunSaved::class, 'Listener@handle');

        $this->assertRegisteredListenersForEvent(PunSaved::class, [
            'Listener@handle',
        ]);

        Event::forgetListener(PunSaved::class, 'Listener@handle');

        $this->assertNoRegisteredListenersForEvent(PunSaved::class);
    }

    /** @test */
    public function it_can_forget_a_listener_using_array_notation()
    {
        Event::listen(PunSaved::class, ['Listener', 'handle']);

        $this->assertRegisteredListenersForEvent(PunSaved::class, [
            ['Listener', 'handle'],
        ]);

        Event::forgetListener(PunSaved::class, ['Listener', 'handle']);

        $this->assertNoRegisteredListenersForEvent(PunSaved::class);
    }

    /** @test */
    public function forgetting_a_listener_doesnt_affect_other_events_or_listeners()
    {
        Event::listen(PunSaved::class, 'SubscriberOne@handle');
        Event::listen(PunSaved::class, 'SubscriberTwo@handle');
        Event::listen(PunDeleted::class, 'SubscriberOne@handle');

        $this->assertRegisteredListenersForEvent(PunSaved::class, [
            'SubscriberOne@handle',
            'SubscriberTwo@handle',
        ]);

        $this->assertRegisteredListenersForEvent(PunDeleted::class, [
            'SubscriberOne@handle',
        ]);

        Event::forgetListener(PunSaved::class, 'SubscriberOne@handle');

        $this->assertRegisteredListenersForEvent(PunSaved::class, [
            'SubscriberTwo@handle',
        ]);

        $this->assertRegisteredListenersForEvent(PunDeleted::class, [
            'SubscriberOne@handle',
        ]);
    }

    private function assertRegisteredListenersForEvent($event, $listeners)
    {
        $this->assertEquals($listeners, array_values(app('events')->getRawListeners()[$event]));
    }

    private function assertNoRegisteredListenersForEvent($event)
    {
        $this->assertCount(0, array_values(app('events')->getRawListeners()[$event]));
    }
}

class PunSaved extends StatamicEvent
{
    //
}

class PunDeleted extends StatamicEvent
{
    //
}
