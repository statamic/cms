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
}
