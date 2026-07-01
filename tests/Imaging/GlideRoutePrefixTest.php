<?php

namespace Tests\Imaging;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\URL;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GlideRoutePrefixTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function tearDown(): void
    {
        URL::enforceTrailingSlashes(false);
        URL::clearUrlCache();

        parent::tearDown();
    }

    #[Test]
    public function it_registers_glide_routes_without_a_double_slash_for_a_path_prefixed_site_with_trailing_slashes()
    {
        URL::enforceTrailingSlashes();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/nl/'],
        ]);

        require __DIR__.'/../../routes/glide.php';

        $uris = collect(Route::getRoutes()->getRoutes())
            ->map->uri()
            ->filter(fn ($uri) => str_contains($uri, 'img/asset'))
            ->values();

        $this->assertContains('nl/img/asset/{container}/{path?}', $uris->all());
        $this->assertEmpty(
            $uris->filter(fn ($uri) => str_contains($uri, '//'))->all(),
            'Glide route prefix should not contain a double slash.'
        );
    }
}
