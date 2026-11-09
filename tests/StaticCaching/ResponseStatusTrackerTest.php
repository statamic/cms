<?php

namespace Tests\StaticCaching;

use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\ResponseStatus;
use Statamic\StaticCaching\ResponseStatusTracker;
use Tests\TestCase;

class ResponseStatusTrackerTest extends TestCase
{
    #[Test]
    public function it_tracks_a_status_per_response()
    {
        $tracker = new ResponseStatusTracker;
        $hit = new Response;
        $miss = new Response;

        $tracker->set($hit, ResponseStatus::HIT);
        $tracker->set($miss, ResponseStatus::MISS);

        $this->assertSame(ResponseStatus::HIT, $tracker->get($hit));
        $this->assertSame(ResponseStatus::MISS, $tracker->get($miss));
        $this->assertSame(ResponseStatus::UNDEFINED, $tracker->get(new Response));
    }

    #[Test]
    public function it_exposes_the_status_through_response_macros()
    {
        $response = new Response;

        $this->assertSame(ResponseStatus::UNDEFINED, $response->staticCacheResponseStatus());
        $this->assertFalse($response->wasStaticallyCached());

        $response->setStaticCacheResponseStatus(ResponseStatus::HIT);

        $this->assertSame(ResponseStatus::HIT, $response->staticCacheResponseStatus());
        $this->assertTrue($response->wasStaticallyCached());
    }

    #[Test]
    public function it_releases_entries_when_the_response_is_garbage_collected()
    {
        $tracker = new ResponseStatusTracker;
        $response = new Response;
        $tracker->set($response, ResponseStatus::HIT);

        $this->assertCount(1, $this->trackedResponses($tracker));

        unset($response);
        gc_collect_cycles();

        $this->assertCount(0, $this->trackedResponses($tracker));
    }

    private function trackedResponses(ResponseStatusTracker $tracker): \WeakMap
    {
        return (new \ReflectionProperty($tracker, 'responses'))->getValue($tracker);
    }
}
