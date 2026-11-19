<?php

namespace Tests\Feature\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Addons\AddonRepository;
use Statamic\Addons\Manifest;
use Statamic\Facades\User;
use Statamic\Licensing\Outpost;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LicensingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $outpostRequests = [];

    public function setUp(): void
    {
        parent::setUp();

        Cache::store('outpost')->flush();

        $this->mock(Manifest::class)->shouldReceive('addons')->andReturn(collect([
            'acme/seo' => [
                'id' => 'acme/seo',
                'name' => 'SEO',
                'version' => '1.0.0',
                'editions' => ['free', 'pro'],
                'marketplaceId' => 999,
                'marketplaceUrl' => 'https://statamic.com/addons/acme/seo',
                'isCommercial' => true,
            ],
        ]));

        Facade::clearResolvedInstance(AddonRepository::class);

        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode([
                'public' => true,
                'site' => ['valid' => true],
                'statamic' => ['valid' => true],
                'packages' => ['acme/seo' => ['valid' => true, 'exists' => true]],
            ])),
        ]));

        $handler->push(Middleware::history($this->outpostRequests));

        $this->app->instance(Outpost::class, new Outpost(new Client(['handler' => $handler])));
    }

    #[Test]
    #[DataProvider('editionProvider')]
    public function it_shows_the_addons_edition($configuredEdition, $expectedEdition)
    {
        config(['statamic.editions.addons.acme/seo' => $configuredEdition]);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('utilities.licensing'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('addons.0.edition', $expectedEdition)
                ->where('addons.0.valid', true)
                ->where('addons.0.invalidReason', null)
                ->where('_statamic.licensing.valid', true)
            );

        $this->assertEquals(['version' => '1.0.0', 'edition' => $expectedEdition], $this->outpostPayload()['packages']['acme/seo']);
    }

    public static function editionProvider()
    {
        return [
            'configured edition' => ['pro', 'pro'],
            'no configured edition' => [null, 'free'],
        ];
    }

    #[Test]
    public function it_shows_an_addon_configured_with_an_edition_it_does_not_have_as_invalid()
    {
        config(['statamic.editions.addons.acme/seo' => 'premium']);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('utilities.licensing'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('addons.0.edition', 'free')
                ->where('addons.0.valid', false)
                ->where('addons.0.invalidReason', "The premium edition set in config/statamic/editions.php doesn't exist")
                ->where('addToCartUrl', fn ($url) => str_contains($url, 'products=999%3Afree'))
                ->where('_statamic.licensing.valid', false)
            );

        $this->assertEquals(['version' => '1.0.0', 'edition' => 'free'], $this->outpostPayload()['packages']['acme/seo']);
    }

    #[Test]
    public function other_cp_pages_still_load_when_an_addon_is_configured_with_an_edition_it_does_not_have()
    {
        config(['statamic.editions.addons.acme/seo' => 'premium']);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('dashboard'))
            ->assertOk();
    }

    private function outpostPayload()
    {
        return json_decode($this->outpostRequests[0]['request']->getBody(), true);
    }
}
