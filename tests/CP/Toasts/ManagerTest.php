<?php

namespace Tests\CP\Toasts;

use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Toasts\Manager;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    #[Test]
    public function toasts_survive_json_session_serialization()
    {
        $handler = new ArraySessionHandler(120);
        $sessionId = str_repeat('a', 40);

        // Flash a toast, then persist the session using json serialization,
        // like a request ending with a redirect would.
        $session = new Store('test', $handler, $sessionId, 'json');
        $session->start();
        (new Manager($session))->success('Saved!');
        $session->save();

        // Read it back on the "next request".
        $session = new Store('test', $handler, $sessionId, 'json');
        $session->start();

        $this->assertSame(
            [['message' => 'Saved!', 'type' => 'success', 'duration' => null]],
            (new Manager($session))->toArray()
        );
    }
}
