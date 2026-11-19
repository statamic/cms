<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\CP\LivePreview;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Entry;
use Statamic\View\View;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AddsHeadersToLivePreviewTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        // The array driver would store entry instances in memory, and we could get false-positive
        // tests by just modifying the entry without actually performing the substitution.
        config(['cache.default' => 'file']);

        EntryFactory::collection('test')->id('1')->slug('alfa')->data(['title' => 'Alfa', 'foo' => 'Alfa foo'])->create();

        $this->withFakeViews();

        $this->viewShouldReturnRaw('test', '');
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Use our View::make() to make sure the Cascade is used.
        // We'd use Route::statamic() but it isn't available at this point.
        Route::get('/test', fn () => View::make('test'))->middleware('statamic.web');
    }

    #[Test]
    public function it_doesnt_set_header_when_single_site()
    {
        $this->setSites(['en' => ['url' => 'http://localhost/', 'locale' => 'en']]);
        $substitute = EntryFactory::collection('test')->id('2')->slug('charlie')->data(['title' => 'Substituted title', 'foo' => 'Substituted foo'])->make();

        LivePreview::tokenize('test-token', $substitute);

        $this->get('/test?token=test-token')
            ->assertHeader('X-Statamic-Live-Preview', true)
            ->assertHeaderMissing('Content-Security-Policy', true);
    }

    #[Test]
    public function it_sets_header_when_multisite()
    {
        config()->set('statamic.system.multisite', true);

        $this->setSites([
            'one' => ['url' => 'http://withport.com:8080/', 'locale' => 'en'],
            'two' => ['url' => 'http://withport.com:8080/fr/', 'locale' => 'fr'],
            'three' => ['url' => 'http://withoutport.com/', 'locale' => 'en'],
            'four' => ['url' => 'http://withoutport.com/fr/', 'locale' => 'fr'],
            'five' => ['url' => 'http://third.com/', 'locale' => 'en'],
            'six' => ['url' => 'http://third.com/fr/', 'locale' => 'fr'],
        ]);

        $substitute = EntryFactory::collection('test')->id('2')->slug('charlie')->data(['title' => 'Substituted title', 'foo' => 'Substituted foo'])->make();

        LivePreview::tokenize('test-token', $substitute);

        $this->get('/test?token=test-token')
            ->assertHeader('X-Statamic-Live-Preview', true)
            ->assertHeader('Content-Security-Policy', 'frame-ancestors http://withport.com:8080 http://withoutport.com http://third.com');
    }
}
