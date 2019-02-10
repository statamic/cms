<?php

namespace Tests\Stache\Stores;

use Mockery;
use Tests\TestCase;
use Statamic\API\Site;
use Statamic\API\Blueprint;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\GlobalsStore;
use Statamic\API\GlobalSet as GlobalsAPI;
use Statamic\Contracts\Data\Globals\GlobalSet;

class GlobalsStoreTest extends TestCase
{
    function setUp()
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStore($this->store = (new GlobalsStore($stache, app('files')))->directory($this->tempDir));
    }

    function tearDown()
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_gets_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);

        $files = Traverser::traverse($this->store);

        $this->assertEquals([
            $this->tempDir.'/one.yaml' => 1234567890,
            $this->tempDir.'/two.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-one.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/three.txt'));
    }

    /** @test */
    function it_makes_global_set_instances_from_cache()
    {
        Blueprint::shouldReceive('find')->with('first_blueprint')
            ->andReturn((new \Statamic\Fields\Blueprint)->setHandle('first_blueprint'));

        $items = $this->store->getItemsFromCache([
            '123' => [
                'handle' => 'one',
                'title' => 'First',
                'blueprint' => 'first_blueprint',
                'sites' => ['en', 'fr'],
                'path' => '/path/to/first.yaml',
                'localizations' => [
                    'en' => [
                        'path' => '/path/to/en/first.yaml',
                        'data' => [
                            'foo' => 'bar',
                        ]
                        ],
                    'fr' => [
                        'path' => '/path/to/fr/first.yaml',
                        'data' => [
                            'foo' => 'le bar',
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEveryItemIsInstanceOf(GlobalSet::class, $items);
        $first = $items->first();
        $this->assertEquals('one', $first->handle());
        $this->assertEquals('one', $first->handle());
        $this->assertEquals('first_blueprint', $first->blueprint()->handle());
        $this->assertEquals(['foo' => 'bar'], $first->data());
        $this->assertEquals(['foo' => 'bar'], $first->in('en')->data());
        $this->assertEquals(['foo' => 'le bar'], $first->in('fr')->data());
        $this->assertEquals('/path/to/first.yaml', $first->initialPath());
        $this->assertEquals('/path/to/en/first.yaml', $first->in('en')->initialPath());
        $this->assertEquals('/path/to/fr/first.yaml', $first->in('fr')->initialPath());
    }

    /** @test */
    function it_makes_global_set_instances_from_files()
    {
        $item = $this->store->createItemFromFile($this->tempDir.'/example.yaml', "id: globals-example\ntitle: Example\ndata:\n  foo: bar");

        $this->assertInstanceOf(GlobalSet::class, $item);
        $this->assertEquals('globals-example', $item->id());
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
        $this->assertEquals(['foo' => 'bar'], $item->data());
    }

    /** @test */
    function it_adds_localized_global_set_data_into_the_base_instance_from_files()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            ]
        ]);

        $set = GlobalsAPI::make()->id('123')->handle('test')->sites(['en', 'fr']);
        $set->in('en', function ($loc) {
            $loc->set('foo', 'bar');
        });
        $this->store->insert($set);
        $this->assertEquals('bar', $set->in('en')->get('foo'));
        $this->assertFalse($set->existsIn('fr'));

        $item = $this->store->createItemFromFile($this->tempDir.'/fr/test.yaml', "foo: le bar");

        $this->assertInstanceOf(GlobalSet::class, $item);
        $this->assertEquals($set, $item);
        $this->assertTrue($item->existsIn('fr'));
    }

    /** @test */
    function it_uses_the_id_as_the_item_key()
    {
        $set = Mockery::mock();
        $set->shouldReceive('id')->andReturn('123');

        $this->assertEquals(
            '123',
            $this->store->getItemKey($set, '/path/to/irrelevant.yaml')
        );
    }

    /** @test */
    function it_gets_the_id_by_handle()
    {
        $this->store->setSitePath('en', '123', $this->tempDir.'/test.yaml');
        $this->store->setSitePath('en', '456', $this->tempDir.'/subdirectory/nested.yaml');

        $this->assertEquals('123', $this->store->getIdByHandle('test'));
        $this->assertEquals('456', $this->store->getIdByHandle('nested'));
    }

    /** @test */
    function it_saves_to_disk_when_using_one_site()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            ]
        ]);

        $global = GlobalsAPI::make()
            ->id('id-new')
            ->handle('new')
            ->title('New Global Set');
        $global->in('en', function ($loc) {
            $loc->data(['foo' => 'bar', 'baz' => 'qux']);
        });

        $this->store->save($global);

        $expected = <<<EOT
id: id-new
title: 'New Global Set'
data:
  foo: bar
  baz: qux

EOT;
        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $expected);
        $this->assertFileNotExists($this->tempDir.'/en/new.yaml');
    }

    /** @test */
    function it_saves_to_disk_when_using_multiple_sites()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/']
            ]
        ]);

        $global = GlobalsAPI::make()
            ->id('id-new')
            ->handle('new')
            ->title('New Global Set')
            ->sites(['en', 'fr']);
        $global->in('en', function ($loc) {
            $loc->data(['foo' => 'bar', 'baz' => 'qux']);
        });
        $global->in('fr', function ($loc) {
            $loc->set('foo', 'le bar');
        });

        $this->store->save($global);

        $expectedBase = <<<EOT
id: id-new
title: 'New Global Set'
sites:
  - en
  - fr

EOT;
        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $expectedBase);
        $this->assertStringEqualsFile($this->tempDir.'/en/new.yaml', "foo: bar\nbaz: qux\n");
        $this->assertStringEqualsFile($this->tempDir.'/fr/new.yaml', "foo: 'le bar'\n");
        $this->assertFileNotExists($this->tempDir.'/de/new.yaml');
    }
}
