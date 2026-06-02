<?php

namespace Tests\StaticCaching;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Tests\TestCase;

class SharedErrorsStaticCachingTest extends TestCase
{
    private ApplicationCacher $cacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacher = new ApplicationCacher($this->app['cache']->store('array'), []);
    }

    #[Test]
    public function the_shared_error_is_scoped_to_the_current_site()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        // Render and share the english 404 first.
        Site::setCurrent('english');
        $this->shareError(404, 'English not found');

        // The french site must not be considered to already have a shared error.
        // Before scoping by site, the english error would leak here and break the
        // localization (e.g. the language picker) for every other site.
        Site::setCurrent('french');
        $this->assertFalse($this->cacher->hasCachedPage($this->sharedErrorRequest(404)));

        $this->shareError(404, 'French not found');

        // Each site now serves its own localized shared error.
        Site::setCurrent('english');
        $this->assertTrue($this->cacher->hasCachedPage($this->sharedErrorRequest(404)));
        $this->assertEquals('English not found', $this->cacher->getCachedPage($this->sharedErrorRequest(404))->content);

        Site::setCurrent('french');
        $this->assertTrue($this->cacher->hasCachedPage($this->sharedErrorRequest(404)));
        $this->assertEquals('French not found', $this->cacher->getCachedPage($this->sharedErrorRequest(404))->content);
    }

    /**
     * Replicates the request that's built when a shared error is written
     * (the Cache middleware's copyError) and read (RendersHttpExceptions's
     * getCachedError) for the current site.
     */
    private function sharedErrorRequest(int $status): Request
    {
        return Request::createFrom(Request::create('http://localhost/'))->fakeStaticCacheStatus($status);
    }

    /**
     * Cache a shared error for the current site, mirroring how the cacher
     * persists pages once the response has been prepared.
     */
    private function shareError(int $status, string $content): void
    {
        $request = $this->sharedErrorRequest($status);
        $response = response($content, $status);

        $this->cacher->cachePage($request, $response);

        // The cacher writes to the store on the ResponsePrepared event, which
        // the framework fires during the real response lifecycle.
        event(new ResponsePrepared($request, $response));
    }
}
