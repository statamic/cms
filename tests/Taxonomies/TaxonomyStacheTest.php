<?php

namespace Tests\Taxonomies;

use Tests\TestCase;
use Statamic\API\Term;
use Statamic\API\Config;
use Statamic\API\Taxonomy;
use Statamic\Stache\Stache;
use Statamic\Stache\Staches\TaxonomyStache;

class TaxonomyStacheTest extends TestCase
{
    /**
     * @var TaxonomyStache
     */
    protected $stache;

    public function setUp()
    {
        $this->markTestSkipped();

        parent::setUp();

        $this->createTaxonomy('tags');

        $this->stache = new TaxonomyStache;
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('statamic.sites', [
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);
    }

    public function test_that_data_is_associated_with_a_new_term()
    {
        $this->stache->associateDataWithTerm('tags', 'foo', 1);
        $this->stache->associateDataWithTerm('tags', 'foo', 2);
        $this->stache->associateDataWithTerm('tags', 'bar', 3);

        $associations = $this->stache->getAssociations();

        $this->assertArrayHasKey('tags/foo', $associations->all());
        $this->assertEquals(2, $associations->get('tags/foo')->count());

        $this->assertArrayHasKey('tags/bar', $associations->all());
        $this->assertEquals(1, $associations->get('tags/bar')->count());
    }

    public function test_that_data_only_gets_associated_once()
    {
        $this->stache->associateDataWithTerm('tags', 'foo', 1);
        $this->stache->associateDataWithTerm('tags', 'foo', 1);

        $this->assertEquals(1, $this->stache->getAssociations('tags/foo')->count());
    }

    public function test_that_terms_get_normalized()
    {
        $this->stache->associateDataWithTerm('tags', 'foo bar', 1);
        $this->stache->associateDataWithTerm('tags', 'Foo-Bar', 2);
        $this->stache->associateDataWithTerm('tags', 'føo bår', 3);

        $associations = $this->stache->getAssociations()->all();

        $this->assertArrayHasKey('tags/foo-bar', $associations);
        $this->assertArrayNotHasKey('tags/foo bar', $associations);
        $this->assertArrayNotHasKey('tags/Foo-Bar', $associations);
        $this->assertArrayNotHasKey('tags/føo bår', $associations);
    }

    public function test_that_term_values_get_saved()
    {
        $this->stache->associateDataWithTerm('tags', 'foo bar', 1);
        $this->stache->associateDataWithTerm('tags', 'Foo-Bar', 2);
        $this->stache->associateDataWithTerm('tags', 'føo bår', 3);

        $values = $this->stache->getTitles();

        $this->assertArrayHasKey('tags/foo-bar', $values->all());
        $this->assertEquals('foo bar', $values->get('tags/foo-bar'));
    }

    public function test_that_first_term_title_saved_is_used_for_subsequent_titles()
    {
        $this->stache->associateDataWithTerm('tags', 'foo bar', 1);
        $this->stache->associateDataWithTerm('tags', 'Foo-Bar', 2);
        $this->stache->associateDataWithTerm('tags', 'føo bår', 3);

        $this->assertEquals('foo bar', $this->stache->getTitles()->get('tags/foo-bar'));
    }

    public function test_that_terms_get_synced()
    {
        $this->stache->associateDataWithTerm('tags', 'bar', 'entry1');
        $this->stache->associateDataWithTerm('tags', 'baz', 'entry1');
        $this->stache->associateDataWithTerm('tags', 'baz', 'entry2');
        $this->stache->associateDataWithTerm('tags', 'qux', 'entry3');

        $this->stache->syncAssociations('entry1', 'tags', ['foo', 'baz']);

        $associations = $this->stache->getAssociations()->all();

        $this->assertArrayHasKey('tags/foo', $associations);
        $this->assertArrayHasKey('tags/baz', $associations);
        $this->assertArrayNotHasKey('tags/bar', $associations);
    }

    public function test_that_terms_with_special_chars_get_synced()
    {
        $this->stache->syncAssociations('entry1', 'tags', ['foo', 'bår']);

        $associations = $this->stache->getAssociations()->all();

        $this->assertArrayHasKey('tags/foo', $associations);
        $this->assertArrayHasKey('tags/bar', $associations);
    }

    public function test_that_term_uris_get_registered()
    {
        Config::set('statamic.routes.taxonomies.tags', '/tags/{slug}');

        $this->stache->addUris('tags', 'foo');

        $this->assertEquals(
            '/tags/foo',
            array_get($this->stache->uris()->toArray(), 'en.tags/foo')
        );
    }

    public function test_that_localized_term_uris_get_registered()
    {
        $this->registerLocalizedTerms();

        $uris = $this->stache->uris()->toArray();

        $this->assertEquals('/tags/foo/field', array_get($uris, 'en.tags/foo'));
        $this->assertEquals('/fr-tags/fr-foo/fr-field', array_get($uris, 'fr.tags/foo'));
        $this->assertEquals('/de-tags/de-foo/de-field', array_get($uris, 'de.tags/foo'));
    }

    private function registerLocalizedTerms()
    {
        Config::set('statamic.routes.taxonomies.tags', [
            'en' => '/tags/{slug}/{field}',
            'fr' => '/fr-tags/{slug}/{field}',
            'de' => '/de-tags/{slug}/{field}'
        ]);

        $term = Term::create('foo')->taxonomy('tags')->with(['field' => 'field'])->get();
        $term->in('fr')->data(['field' => 'fr-field'])->slug('fr-foo');
        $term->in('de')->data(['field' => 'de-field'])->slug('de-foo');

        $this->stache->addTerm('tags', 'foo', $term);

        $this->stache->addUris('tags', 'foo');
    }

    public function test_that_localized_uris_can_be_cleared()
    {
        $this->registerLocalizedTerms();

        $uris = $this->stache->uris()->toArray();
        $this->assertArrayHasKey('en', $uris);
        $this->assertArrayHasKey('fr', $uris);
        $this->assertArrayHasKey('de', $uris);

        $this->stache->clearLocalizedUris();

        $uris = $this->stache->uris()->toArray();
        $this->assertArrayHasKey('en', $uris);
        $this->assertArrayNotHasKey('fr', $uris);
        $this->assertArrayNotHasKey('de', $uris);
    }

    public function test_that_syncing_terms_updates_uris_and_titles()
    {
        Config::set('statamic.routes.taxonomies.tags', '/tags/{slug}');

        $this->stache->associateDataWithTerm('tags', 'bar', 'entry1');
        $this->stache->associateDataWithTerm('tags', 'baz', 'entry1');

        $this->stache->syncAssociations('entry1', 'tags', ['foo', 'baz']);

        $uris = array_get($this->stache->uris()->toArray(), 'en');

        $this->assertEquals('/tags/foo', $uris['tags/foo']);
        $this->assertEquals('/tags/baz', $uris['tags/baz']);
        $this->assertArrayNotHasKey('tags/bar', $uris);

        $titles = $this->stache->getTitles()->all();

        $this->assertArrayHasKey('tags/foo', $titles);
        $this->assertArrayHasKey('tags/baz', $titles);
        $this->assertArrayNotHasKey('tags/bar', $titles);
    }

    public function test_that_removing_data_removes_associations_uris_and_titles_where_necessary()
    {
        Config::set('statamic.routes.taxonomies.tags', '/tags/{slug}');

        $this->stache->associateDataWithTerm('tags', 'foo', 1);
        $this->stache->associateDataWithTerm('tags', 'bar', 1);
        $this->stache->associateDataWithTerm('tags', 'foo', 2);

        $this->stache->removeData(1);

        $uris = array_get($this->stache->uris()->toArray(), 'en');
        $this->assertArrayHasKey('tags/foo', $uris);
        $this->assertArrayNotHasKey('tags/bar', $uris);

        $titles = $this->stache->getTitles()->all();
        $this->assertArrayHasKey('tags/foo', $titles);
        $this->assertArrayNotHasKey('tags/bar', $titles);

        $this->assertEquals(1, $this->stache->getAssociations('tags/foo')->count());
        $this->assertEquals(0 , $this->stache->getAssociations('tags/bar')->count());
        $this->assertArrayNotHasKey('tags/bar', $this->stache->getAssociations()->all());
    }


    private function createTaxonomy($handle)
    {
        $taxonomy = Taxonomy::create($handle);
        $stache = $this->app->make(Stache::class);
        $stache->repo('taxonomies')->setPath($handle, $handle.'.yaml')->setItem($handle, $taxonomy);
        return $taxonomy;
    }
}
