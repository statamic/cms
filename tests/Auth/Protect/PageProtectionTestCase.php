<?php

namespace Tests\Auth\Protect;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PageProtectionTestCase extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withStandardFakeViews();
    }

    protected function requestPageProtectedBy($scheme, $headers = [])
    {
        $this->createPage('test', ['data' => ['protect' => $scheme]]);

        return $this->get('test', $headers);
    }

    protected function createPage($slug, $attributes = [])
    {
        $collection = Collection::make('pages')
            ->routes('{slug}')
            ->template('default')
            ->save();

        EntryFactory::slug($slug)
            ->id($slug)
            ->collection($collection)
            ->data($attributes['data'])
            ->create();
    }
}
