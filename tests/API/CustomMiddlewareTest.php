<?php

namespace Tests\API;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CustomMiddlewareTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app[Kernel::class]->setMiddlewareGroups(array_merge(
            $app[Kernel::class]->getMiddlewareGroups(),
            ['secure-api' => [RequireApiKeyHeader::class, SubstituteBindings::class]],
        ));

        $app['config']->set('statamic.api.middleware', 'secure-api');
    }

    public function setUp(): void
    {
        parent::setUp();

        Facades\Config::set('statamic.api.resources.collections', true);

        $collection = Facades\Collection::make('articles')->save();

        EntryFactory::id('apple')->slug('apple')->collection($collection)->create();
    }

    #[Test]
    public function custom_middleware_is_applied()
    {
        $this
            ->getJson('/api/collections/articles/entries')
            ->assertUnauthorized();

        $this
            ->getJson('/api/collections/articles/entries', ['X-Api-Key' => 'secret'])
            ->assertOk()
            ->assertJsonPath('data.0.id', 'apple');
    }

    #[Test]
    public function custom_middleware_runs_before_the_cached_response_is_returned()
    {
        $this
            ->getJson('/api/collections/articles/entries', ['X-Api-Key' => 'secret'])
            ->assertOk();

        $this
            ->getJson('/api/collections/articles/entries')
            ->assertUnauthorized();
    }
}

class RequireApiKeyHeader
{
    public function handle($request, $next)
    {
        if ($request->header('X-Api-Key') !== 'secret') {
            abort(401);
        }

        return $next($request);
    }
}
