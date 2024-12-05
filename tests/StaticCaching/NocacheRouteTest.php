<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\NoCache\Session;
use Tests\FakesContent;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NocacheRouteTest extends TestCase
{
    use FakesContent;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_nocache_regions_via_a_route()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $count;
            }
        })::register();

        $this->createPage('test', ['with' => ['title' => 'Test']]);

        $session = new Session('http://localhost/test');
        $regionOne = $session->pushRegion('First {{ example_count }} {{ name }} {{ title }}', ['name' => 'Dustin'], 'antlers.html');
        $regionTwo = $session->pushRegion('Second {{ example_count }} {{ name }} {{ title }}', ['name' => 'Will'], 'antlers.html');
        $session->write();

        $this
            ->postJson('/!/nocache', ['url' => 'http://localhost/test'])
            ->assertOk()
            ->assertExactJson([
                'csrf' => csrf_token(),
                'regions' => [
                    $regionOne->key() => 'First 1 Dustin Test',
                    $regionTwo->key() => 'Second 2 Will Test',
                ],
            ]);
    }
}
