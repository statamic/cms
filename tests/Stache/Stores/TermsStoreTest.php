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
    public function it_loads_the_term_file_for_association_keys_in_other_sites()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'de' => ['url' => '/de/'],
        ]);

        Facades\Taxonomy::make('tags')->sites(['en'])->save();

        $term = Facades\Term::make('concerts')->taxonomy('tags')->data(['title' => 'Concerts']);
        $this->parent->store('tags')->save($term);

        $loaded = $this->parent->store('tags')->getItem('de::concerts');

        $this->assertEquals('Concerts', $loaded->in('en')->title());
        $this->assertEquals('Concerts', $loaded->title());

        @unlink($this->directory.'/tags/concerts.yaml');
        file_put_contents($this->directory.'/tags.yaml', "title: Tags\n");
    }
}
