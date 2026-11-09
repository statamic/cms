<?php

namespace Tests\Feature\Utilities;

use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CacheManagerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /**
     * @see https://github.com/statamic/cms/issues/15623
     */
    #[Test]
    public function it_defers_the_image_cache_stats()
    {
        config(['statamic.assets.image_manipulation.cache' => 'glide']);

        Storage::fake('glide');
        Storage::disk('glide')->put('containers/main/foo.jpg/abc.jpg', 'foo');
        Storage::disk('glide')->put('containers/main/bar.jpg/def.jpg', 'barbaz');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('utilities.cache'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('utilities/Cache')
                ->missing('images')
                ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                    ->where('images.count', 2)
                    ->where('images.size', '9 B')
                )
            );
    }

    #[Test]
    public function it_returns_operation_results_once_without_deferring_them()
    {
        config(['statamic.static_caching.strategy' => null]);

        $url = cp_route('utilities.cache');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->from($url)
            ->post(cp_route('utilities.cache.clear-static'))
            ->assertRedirect($url)
            ->assertSessionHas('cacheOperation');

        $message = session('cacheOperation');

        $this->assertMatchesRegularExpression('/^Static page cache cleared in [\\d.]+(?:ms|s)\\.$/', $message);

        $this->get($url)->assertInertia(fn (AssertableInertia $page) => $page
            ->where('operationResult', $message)
            ->where('_toasts.0.message', $message)
            ->missing('images')
        );

        $this->get($url)->assertInertia(fn (AssertableInertia $page) => $page
            ->where('operationResult', null)
        );
    }
}
