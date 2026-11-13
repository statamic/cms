<?php

namespace Tests\Stache\Stores;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\TermsStore;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermsStoreTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $parent;
    private $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->parent = (new TermsStore)->directory(
            $this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/taxonomies')
        );

        Stache::registerStore($this->parent);

        Stache::store('taxonomies')->directory($this->directory);
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $term = Facades\Term::make('test')->taxonomy('tags');
        $term->in('en')->set('title', 'Test');

        $this->parent->store('tags')->save($term);

        $this->assertStringEqualsFile($path = $this->directory.'/tags/test.yaml', $term->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->parent->store('tags')->paths()->get('en::test'));
    }

    #[Test]
    public function the_watcher_removes_every_localization_of_a_term_whose_file_was_deleted()
    {
        config(['statamic.stache.watcher' => true]);

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);
        $taxonomyFile = $this->directory.'/tags.yaml';
        $taxonomyYaml = file_get_contents($taxonomyFile);
        Facades\Taxonomy::make('tags')->sites(['en', 'fr'])->save();

        $path = $this->directory.'/tags/watcher-test.yaml';
        file_put_contents($path, "title: 'Watcher test'\nlocalizations:\n  fr:\n    title: 'Test du watcher'\n");

        try {
            $store = $this->parent->store('tags');
            $this->assertEquals($path, $store->paths()->get('en::watcher-test'));
            $this->assertEquals($path, $store->paths()->get('fr::watcher-test'));

            unlink($path);
            $store->handleFileChanges();

            $this->assertNull($store->paths()->get('en::watcher-test'));
            $this->assertNull($store->paths()->get('fr::watcher-test'));
        } finally {
            @unlink($path);
            file_put_contents($taxonomyFile, $taxonomyYaml);
        }
    }
}
