<?php

namespace Tests\Stache\Stores;

use Statamic\Facades;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\TermsStore;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermsStoreTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->parent = (new TermsStore)->directory(
            $this->directory = __DIR__.'/../__fixtures__/content/taxonomies'
        );

        Stache::registerStore($this->parent);

        Stache::store('taxonomies')->directory($this->directory);
    }

    /** @test */
    public function it_saves_to_disk()
    {
        $term = Facades\Term::make('test')->taxonomy('tags');
        $term->in('en')->set('title', 'Test');

        $this->parent->store('tags')->save($term);

        $this->assertFileEqualsString($path = $this->directory.'/tags/test.yaml', $term->fileContents());
        @unlink($path);
        $this->assertFileNotExists($path);

        $this->assertEquals('en::'.$path, $this->parent->store('tags')->paths()->get('en::test'));
    }
}
