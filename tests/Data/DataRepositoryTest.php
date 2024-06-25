<?php

namespace Tests\Data;

use Facades\Tests\Factories\EntryFactory;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Data\DataRepository;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DataRepositoryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $data;

    // Mocking method_exists, courtesy of https://stackoverflow.com/a/37928161
    public static $functions;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = (new DataRepository);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::$functions = null;
    }

    #[Test]
    public function it_splits_the_repository_and_key_if_the_repository_exists()
    {
        $this->data->setRepository('entry', new \stdClass);
        $this->assertEquals(['entry', '123'], $this->data->splitReference('entry::123'));
        $this->assertEquals([null, 'unknown::123'], $this->data->splitReference('unknown::123'));
    }

    #[Test]
    public function it_splits_the_repository_and_key_even_if_there_are_multiple_delimiters()
    {
        $this->data->setRepository('asset', new \stdClass);

        $this->assertEquals(['asset', 'main::foo/bar'], $this->data->splitReference('asset::main::foo/bar'));
        $this->assertEquals([null, 'unknown::main::foo/bar'], $this->data->splitReference('unknown::main::foo/bar'));
    }

    #[Test]
    public function it_returns_the_key_as_is_if_theres_no_delimiter()
    {
        $this->assertEquals([null, '123'], $this->data->splitReference('123'));
    }

    #[Test]
    public function it_proxies_find_to_a_repository()
    {
        $this->app->instance('FooRepository', Mockery::mock('FooRepository', function ($m) {
            $m->shouldReceive('find')->once()->with('123')->andReturn('test');
        }));

        $this->data->setRepository('foo', 'FooRepository');

        $this->assertEquals('test', $this->data->find('foo::123'));
    }

    #[Test]
    public function it_bails_early_when_finding_null()
    {
        $this->app->instance('FooRepository', Mockery::mock('FooRepository', function ($m) {
            $m->shouldNotReceive('find');
        }));

        $this->data->setRepository('foo', 'FooRepository');

        $this->assertNull($this->data->find(null));
    }

    #[Test]
    public function when_a_repository_key_isnt_provided_it_will_loop_through_repositories()
    {
        $this->mockMethodExists();

        $this->app->instance('FooRepository', Mockery::mock('FooRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->with('FooRepository', 'find')->once()->andReturnTrue();
            $m->shouldReceive('find')->once()->with('123')->andReturnNull();
        }));
        $this->app->instance('WithoutFindMethodRepository', Mockery::mock('WithoutFindMethodRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->with('WithoutFindMethodRepository', 'find')->once()->andReturnFalse();
            $m->shouldReceive('find')->never();
        }));
        $this->app->instance('BarRepository', Mockery::mock('BarRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->with('BarRepository', 'find')->once()->andReturnTrue();
            $m->shouldReceive('find')->once()->with('123')->andReturn('test');
        }));
        $this->app->instance('BazRepository', Mockery::mock('BazRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->never();
            $m->shouldReceive('find')->never();
        }));

        $this->data
            ->setRepository('foo', 'FooRepository')
            ->setRepository('withoutFind', 'WithoutFindMethodRepository')
            ->setRepository('bar', 'BarRepository')
            ->setRepository('baz', 'BazRepository');

        $this->assertEquals('test', $this->data->find('123'));
    }

    #[Test]
    #[DataProvider('findByRequestUrlProvider')]
    public function it_finds_by_request_url($requestUrl, $entryId)
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $this->findByRequestUrlTest($requestUrl, $entryId);
    }

    #[Test]
    #[DataProvider('findByRequestUrlNoRootSiteProvider')]
    public function it_finds_by_request_url_with_no_root_site($requestUrl, $entryId)
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/en/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $this->findByRequestUrlTest($requestUrl, $entryId);
    }

    public static function findByRequestUrlProvider()
    {
        return [
            'root' => ['http://localhost', 'home'],
            'root with slash' => ['http://localhost/', 'home'],
            'root with query' => ['http://localhost?a=b', 'home'],
            'root with query and slash' => ['http://localhost/?a=b', 'home'],

            'dir' => ['http://localhost/foo', 'foo'],
            'dir with slash' => ['http://localhost/foo/', 'foo'],
            'dir with query' => ['http://localhost/foo?a=b', 'foo'],
            'dir with query and slash' => ['http://localhost/foo/?a=b', 'foo'],

            'missing' => ['http://localhost/unknown', null],
            'missing with slash' => ['http://localhost/unknown/', null],
            'missing with query' => ['http://localhost/unknown?a=b', null],
            'missing with query and slash' => ['http://localhost/unknown/?a=b', null],
        ];
    }

    public static function findByRequestUrlNoRootSiteProvider()
    {
        return [
            'root' => ['http://localhost', null],
            'root with slash' => ['http://localhost/', null],
            'root with query' => ['http://localhost?a=b', null],
            'root with query and slash' => ['http://localhost/?a=b', null],

            'missing' => ['http://localhost/unknown', null],
            'missing with slash' => ['http://localhost/unknown/', null],
            'missing with query' => ['http://localhost/unknown?a=b', null],
            'missing with query and slash' => ['http://localhost/unknown/?a=b', null],

            'home' => ['http://localhost/en', 'home'],
            'home with slash' => ['http://localhost/en/', 'home'],
            'home with query' => ['http://localhost/en?a=b', 'home'],
            'home with query and slash' => ['http://localhost/en/?a=b', 'home'],

            'fr home' => ['http://localhost/fr', 'le-home'],
            'fr home with slash' => ['http://localhost/fr/', 'le-home'],
            'fr home with query' => ['http://localhost/fr?a=b', 'le-home'],
            'fr home with query and slash' => ['http://localhost/fr/?a=b', 'le-home'],

            'dir' => ['http://localhost/en/foo', 'foo'],
            'dir with slash' => ['http://localhost/en/foo/', 'foo'],
            'dir with query' => ['http://localhost/en/foo?a=b', 'foo'],
            'dir with query and slash' => ['http://localhost/en/foo/?a=b', 'foo'],

            'fr dir' => ['http://localhost/fr/le-foo', 'le-foo'],
            'fr dir with slash' => ['http://localhost/fr/le-foo/', 'le-foo'],
            'fr dir with query' => ['http://localhost/fr/le-foo?a=b', 'le-foo'],
            'fr dir with query and slash' => ['http://localhost/fr/le-foo/?a=b', 'le-foo'],
        ];
    }

    private function findByRequestUrlTest($requestUrl, $entryId)
    {
        $this->data->setRepository('entry', EntryRepository::class);

        $c = tap(Collection::make('pages')->sites(['english', 'french'])->routes('{slug}')->structureContents(['root' => true]))->save();
        EntryFactory::collection('pages')->id('home')->slug('home')->locale('english')->create();
        EntryFactory::collection('pages')->id('foo')->slug('foo')->locale('english')->create();
        EntryFactory::collection('pages')->id('le-home')->slug('le-home')->locale('french')->origin('home')->create();
        EntryFactory::collection('pages')->id('le-foo')->slug('le-foo')->locale('french')->origin('foo')->create();

        $c->structure()->in('english')->tree([
            ['entry' => 'home'],
            ['entry' => 'foo'],
        ])->save();
        $c->structure()->in('french')->tree([
            ['entry' => 'le-home'],
            ['entry' => 'le-foo'],
        ])->save();

        $found = $this->data->findByRequestUrl($requestUrl);

        if ($entryId) {
            $this->assertNotNull($found);
            $this->assertEquals($entryId, $found->id());
        } else {
            $this->assertNull($found);
        }
    }

    private function mockMethodExists()
    {
        static::$functions = Mockery::mock();
    }
}

namespace Statamic\Data;

function method_exists($object, $method)
{
    // Once this file is loaded, Statamic\Data\method_exists will exist and affect other
    // tests being run. In tearDown, we'll remove it so it can fall back to the global.
    $functions = \Tests\Data\DataRepositoryTest::$functions;

    return $functions
        ? $functions->method_exists($object, $method)
        : \method_exists($object, $method);
}
