<?php

namespace Tests\Search;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Search\Comb\Index as CombIndex;
use Statamic\Search\IndexManager;
use Statamic\Search\Null\NullIndex;
use Tests\TestCase;

class IndexManagerTest extends TestCase
{
    #[Test]
    public function it_gets_indexes()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr/'],
            'de' => ['url' => '/de/'],
        ]);

        config(['statamic.search.indexes' => [
            'foo' => [
                'driver' => 'null',
            ],
            'bar' => [
                'driver' => 'local',
                'sites' => ['en', 'fr'],
            ],
            'baz' => [
                'driver' => 'local',
                'sites' => 'all',
            ],
        ]]);

        $manager = new IndexManager($this->app);

        $this->assertEquals(['foo', 'bar_en', 'bar_fr', 'baz_en', 'baz_fr', 'baz_de', 'cp'], $manager->all()->map->name()->values()->all());

        $this->assertInstanceOf(NullIndex::class, $foo = $manager->index('foo'));
        $this->assertEquals('foo', $foo->name());
        $this->assertNull($foo->locale());

        $this->assertInstanceOf(CombIndex::class, $barEn = $manager->index('bar', 'en'));
        $this->assertEquals('bar_en', $barEn->name());
        $this->assertEquals('en', $barEn->locale());

        $this->assertInstanceOf(CombIndex::class, $barFr = $manager->index('bar', 'fr'));
        $this->assertEquals('bar_fr', $barFr->name());
        $this->assertEquals('fr', $barFr->locale());

        try {
            $manager->index('bar', 'de');
            $this->fail('Expected exception to be thrown.');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Search index [bar] has not been configured for the [de] site.', $e->getMessage());
        }

        Site::setCurrent('fr');
        $this->assertInstanceOf(CombIndex::class, $bar = $manager->index('bar'));
        $this->assertEquals('bar_fr', $bar->name());
        $this->assertEquals('fr', $bar->locale());

        Site::setCurrent('en');
        $this->assertInstanceOf(CombIndex::class, $bar = $manager->index('bar'));
        $this->assertEquals('bar_en', $bar->name());
        $this->assertEquals('en', $bar->locale());

        Site::setCurrent('de');
        try {
            $manager->index('bar');
            $this->fail('Expected exception to be thrown.');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Search index [bar] has not been configured for the [de] site.', $e->getMessage());
        }
    }

    #[Test]
    public function it_builds_the_cp_index_if_it_doesnt_exist_in_the_config()
    {
        config(['statamic.search.indexes' => [
            'default' => [
                'driver' => 'local',
                'searchables' => 'content',
                'fields' => ['title'],
            ],
        ]]);

        $manager = new IndexManager($this->app);

        $this->assertEquals(['default', 'cp'], $manager->all()->map->name()->values()->all());

        $this->assertEquals([
            'fields' => ['title'],
            'path' => storage_path('statamic/search'),
            'driver' => 'local',
            'searchables' => ['content', 'users', 'addons'],
        ], $manager->index('cp')->config());
    }

    #[Test]
    public function it_uses_the_cp_index_from_the_config_if_it_exists()
    {
        config(['statamic.search.indexes' => [
            'default' => [
                'driver' => 'local',
                'searchables' => 'content',
                'fields' => ['title'],
            ],
            'cp' => [
                'driver' => 'local',
                'searchables' => ['collections:pages', 'collections:blog'],
                'fields' => ['title', 'excerpt'],
            ],
        ]]);

        $manager = new IndexManager($this->app);

        $this->assertEquals(['default', 'cp'], $manager->all()->map->name()->values()->all());

        $this->assertEquals([
            'fields' => ['title', 'excerpt'],
            'path' => storage_path('statamic/search'),
            'driver' => 'local',
            'searchables' => ['collections:pages', 'collections:blog'],
        ], $manager->index('cp')->config());
    }
}
