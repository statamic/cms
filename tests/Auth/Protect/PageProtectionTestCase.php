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
        $this->createPage('test', ['with' => ['protect' => $scheme]]);

        return $this->get('test', $headers);
    }

    protected function createPage($slug, $attributes = [])
    {
        $collection = Collection::create('pages');
        $collection->data(['route' => '{slug}']);
        $collection->save();

        return Entry::create($slug)
            ->id($slug)
            ->collection('pages')
            ->path(array_get($attributes, 'path', $slug.'.html'))
            ->with(array_get($attributes, 'with', []))
            ->save();
    }
}
