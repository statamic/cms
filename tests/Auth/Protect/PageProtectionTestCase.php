<?php

namespace Tests\Auth\Protect;

use Tests\TestCase;
use Tests\FakesViews;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class PageProtectionTestCase extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp()
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
        $collection = Collection::create('pages')
            ->route('{slug}')
            ->template('default');

        Entry::create()
            ->id($slug)
            ->collection($collection)
            ->in(function ($loc) use ($slug, $attributes) {
                $loc->slug($slug)->data($attributes['data']);
            })->save();
    }
}
