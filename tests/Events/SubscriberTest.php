<?php

namespace Tests\Events;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\Event as StatamicEvent;
use Statamic\Events\Subscriber as StatamicSubscriber;
use Statamic\Facades\Blink;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Blink::put(PunSaved::class, 0);
        Blink::put(PunDeleted::class, 0);
        Blink::put(StaticCachePunSubscriber::class, 0);
        Blink::put(EmailPunSubscriber::class, 0);
    }

    #[Test]
    public function it_handles_dispatched_events()
    {
        Event::subscribe(StaticCachePunSubscriber::class);

        $this->assertSubscriberNotHandled(StaticCachePunSubscriber::class);
        $this->assertEventHandledCount(0, PunSaved::class);
        $this->assertEventHandledCount(0, PunDeleted::class);

        PunSaved::dispatch();
        PunDeleted::dispatch();
        PunDeleted::dispatch();

        $this->assertSubscriberHandled(StaticCachePunSubscriber::class);
        $this->assertEventHandledCount(1, PunSaved::class);
        $this->assertEventHandledCount(2, PunDeleted::class);
    }

    #[Test]
    public function it_can_temporarily_disable_and_re_enable_subscriber_handled_listeners()
    {
        Event::subscribe(StaticCachePunSubscriber::class);

        $this->assertEventHandledCount(0, PunSaved::class);
        $this->assertEventHandledCount(0, PunDeleted::class);

        PunSaved::dispatch();
        PunDeleted::dispatch();

        $this->assertEventHandledCount(1, PunSaved::class);
        $this->assertEventHandledCount(1, PunDeleted::class);

        StaticCachePunSubscriber::disable();
        PunSaved::dispatch();
        PunDeleted::dispatch();

        $this->assertEventHandledCount(1, PunSaved::class);
        $this->assertEventHandledCount(1, PunDeleted::class);

        StaticCachePunSubscriber::enable();
        PunSaved::dispatch();
        PunDeleted::dispatch();

        $this->assertEventHandledCount(2, PunSaved::class);
        $this->assertEventHandledCount(2, PunDeleted::class);
    }

    #[Test]
    public function it_can_temporarily_disable_listeners_on_code_run_within_a_callback()
    {
        Event::subscribe(StaticCachePunSubscriber::class);

        $this->assertEventHandledCount(0, PunSaved::class);
        $this->assertEventHandledCount(0, PunDeleted::class);

        PunSaved::dispatch();
        PunDeleted::dispatch();

        $this->assertEventHandledCount(1, PunSaved::class);
        $this->assertEventHandledCount(1, PunDeleted::class);

        StaticCachePunSubscriber::withoutListeners(function () {
            PunSaved::dispatch();
            PunDeleted::dispatch();
        });

        $this->assertEventHandledCount(1, PunSaved::class);
        $this->assertEventHandledCount(1, PunDeleted::class);

        PunSaved::dispatch();
        PunDeleted::dispatch();

        $this->assertEventHandledCount(2, PunSaved::class);
        $this->assertEventHandledCount(2, PunDeleted::class);
    }

    #[Test]
    public function disabling_one_subscriber_does_not_affect_other_subscribers()
    {
        Event::subscribe(StaticCachePunSubscriber::class);
        Event::subscribe(EmailPunSubscriber::class);

        $this->assertEventHandledCount(0, PunSaved::class);
        $this->assertEventHandledCount(0, PunDeleted::class);

        PunSaved::dispatch();
        PunDeleted::dispatch();

        $this->assertEventHandledCount(2, PunSaved::class);
        $this->assertEventHandledCount(1, PunDeleted::class);

        StaticCachePunSubscriber::disable();
        PunSaved::dispatch();
        PunDeleted::dispatch();

        $this->assertEventHandledCount(3, PunSaved::class);
        $this->assertEventHandledCount(1, PunDeleted::class);
    }

    private function assertSubscriberNotHandled($subscriber)
    {
        $this->assertEquals(0, Blink::get($subscriber));
    }

    private function assertSubscriberHandled($subscriber)
    {
        $this->assertNotEquals(0, Blink::get($subscriber));
    }

    private function assertEventHandledCount($count, $event)
    {
        $this->assertEquals($count, Blink::get($event));
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

class StaticCachePunSubscriber extends StatamicSubscriber
{
    public $listeners = [
        PunSaved::class => self::class.'@handleSaved', // string notation
        PunDeleted::class => [self::class, 'handleDeleted'], // array notation
    ];

    public function handleSaved(PunSaved $event)
    {
        Blink::increment(PunSaved::class);
        Blink::increment(self::class);
    }

    public function handleDeleted(PunDeleted $event)
    {
        Blink::increment(PunDeleted::class);
        Blink::increment(self::class);
    }
}

class EmailPunSubscriber extends StatamicSubscriber
{
    public $listeners = [
        PunSaved::class => 'handleSaved', // string notation with implicit class
    ];

    public function handleSaved(PunSaved $event)
    {
        Blink::increment(PunSaved::class);
        Blink::increment(self::class);
    }
}
