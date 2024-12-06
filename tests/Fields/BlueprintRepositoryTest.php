<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\BlueprintNotFoundException;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Support\FileCollection;
use Tests\TestCase;

class BlueprintRepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(BlueprintRepository::class)
            ->setDirectory('/path/to/resources/blueprints');

        Facades\Blueprint::swap($this->repo);
    }

    #[Test]
    public function it_gets_a_blueprint()
    {
        $contents = <<<'EOT'
title: Test
tabs:
  main:
    fields:
      - one
      - two
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/blueprints/test.yaml')->once()->andReturn($contents);

        $blueprint = $this->repo->find('test');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('test', $blueprint->handle());
        $this->assertNull($blueprint->namespace());
        $this->assertEquals([
            'title' => 'Test',
            'tabs' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_gets_a_blueprint_in_a_namespace()
    {
        $contents = <<<'EOT'
title: Test
tabs:
  main:
    fields:
      - one
      - two
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/baz.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/blueprints/foo/bar/baz.yaml')->once()->andReturn($contents);

        $blueprint = $this->repo->find('foo.bar.baz');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('baz', $blueprint->handle());
        $this->assertEquals('foo.bar', $blueprint->namespace());
        $this->assertEquals([
            'title' => 'Test',
            'tabs' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_gets_a_blueprint_in_a_custom_namespace()
    {
        $contents = <<<'EOT'
title: Test
tabs:
  main:
    fields:
      - one
      - two
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/baz.yaml')->once()->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/vendor/foo/baz.yaml')->once()->andReturnFalse();
        File::shouldReceive('get')->with('/path/to/resources/blueprints/foo/baz.yaml')->once()->andReturn($contents);

        Facades\Blueprint::addNamespace('foo', '/path/to/resources/blueprints/foo');

        $blueprint = $this->repo->find('foo::baz');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('baz', $blueprint->handle());
        $this->assertEquals('foo', $blueprint->namespace());
        $this->assertEquals([
            'title' => 'Test',
            'tabs' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_gets_an_overidden_blueprint_in_a_custom_namespace()
    {
        $contents = <<<'EOT'
title: Test
tabs:
  main:
    fields:
      - one
      - two
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/vendor/foo/baz.yaml')->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/blueprints/vendor/foo/baz.yaml')->once()->andReturn($contents);

        Facades\Blueprint::addNamespace('foo', '/path/to/resources/blueprints/foo');

        $blueprint = $this->repo->find('foo::baz');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('baz', $blueprint->handle());
        $this->assertEquals('foo', $blueprint->namespace());
        $this->assertEquals([
            'title' => 'Test',
            'tabs' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_returns_null_if_blueprint_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('unknown'));
    }

    #[Test]
    public function it_returns_null_if_blueprint_doesnt_exist_in_a_namespace()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('foo.bar.unknown'));
    }

    #[Test]
    public function it_gets_fallback_blueprint()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();

        $fallback = $this->repo->make();
        $this->repo->setFallback('unknown', function () use ($fallback) {
            return $fallback;
        });

        $this->assertSame($fallback, $this->repo->find('unknown'));
        $this->assertEquals('unknown', $fallback->handle());
        $this->assertNull($fallback->namespace());
    }

    #[Test]
    public function it_gets_namespaced_fallback_blueprint()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnNull();

        $fallback = $this->repo->make();
        $this->repo->setFallback('foo.bar.unknown', function () use ($fallback) {
            return $fallback;
        });

        $this->assertSame($fallback, $this->repo->find('foo.bar.unknown'));
        $this->assertEquals('unknown', $fallback->handle());
        $this->assertEquals('foo.bar', $fallback->namespace());
    }

    #[Test]
    public function getting_a_non_existent_namespaced_blueprint_will_not_return_the_non_namespaced_fallback()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnNull();

        $fallback = $this->repo->make();
        $this->repo->setFallback('unknown', function () use ($fallback) {
            return $fallback;
        });

        $this->assertNull($this->repo->find('foo.bar.unknown'));
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $expectedYaml = <<<'EOT'
title: 'Test Blueprint'
tabs:
  one:
    display: One
    fields:
      -
        handle: foo
        field: foo.bar
        config:
          display: Foo
          foo: bar
      -
        handle: bar
        field:
          type: bar
          display: Bar
          bar: foo

EOT;

        File::shouldReceive('put')->with('/path/to/resources/blueprints/the_test_blueprint.yaml', $expectedYaml)->once();

        $blueprint = (new Blueprint)->setHandle('the_test_blueprint')->setContents([
            'title' => 'Test Blueprint',
            'tabs' => [
                'one' => [
                    'display' => 'One',
                    'fields' => [
                        [
                            'handle' => 'foo',
                            'field' => 'foo.bar',
                            'config' => [
                                'display' => 'Foo',
                                'foo' => 'bar',
                            ],
                        ],
                        [
                            'handle' => 'bar',
                            'field' => [
                                'type' => 'bar',
                                'display' => 'Bar',
                                'bar' => 'foo',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->repo->save($blueprint);
    }

    #[Test]
    public function it_gets_blueprints_in_a_namespace()
    {
        $dir = '/path/to/resources/blueprints/collections/blog';
        $overrideDir = '/path/to/resources/blueprints/vendor/collections/blog';
        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('exists')->with($overrideDir)->once()->andReturnFalse();
        File::shouldReceive('getFilesByType')->with($dir, 'yaml')->once()->andReturn(
            new FileCollection([$dir.'/first.yaml', $dir.'/second.yaml'])
        );
        File::shouldReceive('get')->with($dir.'/first.yaml')->once()->andReturn('title: First Blueprint');
        File::shouldReceive('get')->with($dir.'/second.yaml')->once()->andReturn('title: Second Blueprint');

        $blueprints = $this->repo->in('collections.blog');

        $this->assertInstanceOf(Collection::class, $blueprints);
        $this->assertCount(2, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals(['first', 'second'], $blueprints->keys()->all());
        $this->assertEquals(['first', 'second'], $blueprints->map->handle()->values()->all());
        $this->assertEquals(['collections.blog', 'collections.blog'], $blueprints->map->namespace()->values()->all());
        $this->assertEquals(['First Blueprint', 'Second Blueprint'], $blueprints->map->title()->values()->all());
    }

    #[Test]
    public function it_returns_empty_collection_if_directory_doesnt_exist()
    {
        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('getFilesByType')->with('/path/to/resources/blueprints/test', 'yaml')->once()->andReturn(new FileCollection);
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/vendor/test')->once()->andReturnFalse();

        $all = $this->repo->in('test');

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }

    #[Test]
    public function it_gets_blueprints_in_a_custom_namespace()
    {
        $dir = '/path/to/custom';
        $overrideDir = '/path/to/resources/blueprints/vendor/custom';
        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('exists')->with($overrideDir)->once()->andReturnFalse();
        File::shouldReceive('getFilesByType')->with($dir, 'yaml')->once()->andReturn(
            new FileCollection([$dir.'/first.yaml', $dir.'/second.yaml'])
        );
        File::shouldReceive('get')->with($dir.'/first.yaml')->once()->andReturn('title: First Blueprint');
        File::shouldReceive('get')->with($dir.'/second.yaml')->once()->andReturn('title: Second Blueprint');

        $this->repo->addNamespace('custom', $dir);

        $blueprints = $this->repo->in('custom');

        $this->assertInstanceOf(Collection::class, $blueprints);
        $this->assertCount(2, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals(['first', 'second'], $blueprints->keys()->all());
        $this->assertEquals(['first', 'second'], $blueprints->map->handle()->values()->all());
        $this->assertEquals(['custom', 'custom'], $blueprints->map->namespace()->values()->all());
        $this->assertEquals(['First Blueprint', 'Second Blueprint'], $blueprints->map->title()->values()->all());
    }

    #[Test]
    public function it_gets_blueprints_in_a_custom_namespace_with_overrides()
    {
        File::shouldReceive('withAbsolutePaths')->twice()->andReturnSelf();
        $dir = '/path/to/custom';
        $overrideDir = '/path/to/resources/blueprints/vendor/custom';
        File::shouldReceive('exists')->with($overrideDir)->andReturnTrue();
        File::shouldReceive('getFilesByType')->with($dir, 'yaml')->once()->andReturn(
            new FileCollection([$dir.'/first.yaml', $dir.'/second.yaml'])
        );
        File::shouldReceive('getFilesByType')->with($overrideDir, 'yaml')->once()->andReturn(
            new FileCollection([
                $overrideDir.'/second.yaml',
                $overrideDir.'/third.yaml',
            ])
        );
        File::shouldReceive('get')->with($dir.'/first.yaml')->once()->andReturn('title: First Blueprint');
        File::shouldReceive('get')->with($overrideDir.'/second.yaml')->once()->andReturn('title: Overridden Second Blueprint');
        File::shouldReceive('get')->with($overrideDir.'/third.yaml')->once()->andReturn('title: Third Blueprint only in overrides');

        $this->repo->addNamespace('custom', $dir);

        $blueprints = $this->repo->in('custom');

        $this->assertInstanceOf(Collection::class, $blueprints);
        $this->assertCount(3, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals(['first', 'second', 'third'], $blueprints->keys()->all());
        $this->assertEquals(['first', 'second', 'third'], $blueprints->map->handle()->values()->all());
        $this->assertEquals(['custom', 'custom', 'custom'], $blueprints->map->namespace()->values()->all());
        $this->assertEquals(['First Blueprint', 'Overridden Second Blueprint', 'Third Blueprint only in overrides'], $blueprints->map->title()->values()->all());
    }

    #[Test]
    public function it_gets_blueprints_in_a_custom_namespace_where_there_are_no_original_files_but_only_overrides()
    {
        File::shouldReceive('withAbsolutePaths')->twice()->andReturnSelf();
        $dir = '/path/to/custom';
        $overrideDir = '/path/to/resources/blueprints/vendor/custom';
        File::shouldReceive('exists')->with($overrideDir)->andReturnTrue();
        File::shouldReceive('getFilesByType')->with($dir, 'yaml')->once()->andReturn(new FileCollection);
        File::shouldReceive('getFilesByType')->with($overrideDir, 'yaml')->once()->andReturn(
            new FileCollection([
                $overrideDir.'/first.yaml',
                $overrideDir.'/second.yaml',
            ])
        );
        File::shouldReceive('get')->with($overrideDir.'/first.yaml')->once()->andReturn('title: First Blueprint only in overrides');
        File::shouldReceive('get')->with($overrideDir.'/second.yaml')->once()->andReturn('title: Second Blueprint only in overrides');

        $this->repo->addNamespace('custom', $dir);

        $blueprints = $this->repo->in('custom');

        $this->assertInstanceOf(Collection::class, $blueprints);
        $this->assertCount(2, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals(['first', 'second'], $blueprints->keys()->all());
        $this->assertEquals(['first', 'second'], $blueprints->map->handle()->values()->all());
        $this->assertEquals(['custom', 'custom'], $blueprints->map->namespace()->values()->all());
        $this->assertEquals(['First Blueprint only in overrides', 'Second Blueprint only in overrides'], $blueprints->map->title()->values()->all());
    }

    #[Test]
    public function it_resets_a_namespaced_blueprint()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/vendor/foo/test.yaml')->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/blueprints/vendor/foo/test.yaml')->once()->andReturn('title: Overwritten Test Blueprint');
        File::shouldReceive('delete')->once();

        $this->repo->addNamespace('foo', 'foo');
        $blueprint = $this->repo->find('foo::test');

        $this->repo->reset($blueprint);
    }

    #[Test]
    public function it_sets_the_namespace_when_passed_when_making()
    {
        $blueprint = $this->repo->make('test::handle');

        $this->assertSame($blueprint->namespace(), 'test');
        $this->assertSame($blueprint->handle(), 'handle');
    }

    #[Test]
    public function it_gets_a_blueprint_using_find_or_fail()
    {
        $contents = <<<'EOT'
title: Test
tabs:
  main:
    fields:
      - one
      - two
EOT;

        File::shouldReceive('exists')->with('/path/to/resources/blueprints/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/blueprints/test.yaml')->once()->andReturn($contents);

        $blueprint = $this->repo->findOrFail('test');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('test', $blueprint->handle());
    }

    #[Test]
    public function find_or_fail_throws_exception_when_blueprint_does_not_exist()
    {
        $this->expectException(BlueprintNotFoundException::class);
        $this->expectExceptionMessage('Blueprint [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }
}
