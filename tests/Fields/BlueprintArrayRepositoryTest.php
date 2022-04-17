<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Support\FileCollection;
use Tests\TestCase;

class BlueprintArrayRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(BlueprintRepository::class)
            ->setDirectory([
                '/path/to/resources/blueprints',
                '/another/path/to/resources/blueprints',
            ]);

        Facades\Blueprint::swap($this->repo);
    }

    /** @test */
    public function it_gets_a_blueprint_from_additional_directory()
    {
        $contents = <<<'EOT'
title: Test
sections:
  main:
    fields:
      - one
      - two
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/test.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/another/path/to/resources/blueprints/test.yaml')->once()->andReturn($contents);

        $blueprint = $this->repo->find('test');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('test', $blueprint->handle());
        $this->assertNull($blueprint->namespace());
        $this->assertEquals([
            'title' => 'Test',
            'sections' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    /** @test */
    public function it_gets_a_blueprint_in_a_namespace_in_the_additional_directory()
    {
        $contents = <<<'EOT'
title: Test
sections:
  main:
    fields:
      - one
      - two
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/baz.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/foo/bar/baz.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/another/path/to/resources/blueprints/foo/bar/baz.yaml')->once()->andReturn($contents);

        $blueprint = $this->repo->find('foo.bar.baz');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('baz', $blueprint->handle());
        $this->assertEquals('foo.bar', $blueprint->namespace());
        $this->assertEquals([
            'title' => 'Test',
            'sections' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    /** @test */
    public function it_gets_a_blueprint_path_from_additional_directory()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/test.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/test.yaml')->once()->andReturnTrue();

        $path = $this->repo->path('test');

        $this->assertEquals('/another/path/to/resources/blueprints/test.yaml', $path);
    }

    /** @test */
    public function it_gets_a_blueprint_path_in_a_namespace_in_additional_directory()
    {
        $contents = <<<'EOT'
title: Test
sections:
  main:
    fields:
      - one
      - two
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/baz.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/foo/bar/baz.yaml')->once()->andReturnTrue();

        $path = $this->repo->path('foo.bar.baz');

        $this->assertEquals('/another/path/to/resources/blueprints/foo/bar/baz.yaml', $path);
    }

    /** @test */
    public function it_returns_null_if_path_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();

        $path = $this->repo->path('unknown');

        $this->assertNull($path);
    }

    /** @test */
    public function it_returns_null_if_blueprint_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    public function it_returns_null_if_blueprint_doesnt_exist_in_a_namespace()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('foo.bar.unknown'));
    }

    /** @test */
    public function it_gets_fallback_blueprint()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();

        $fallback = $this->repo->make();
        $this->repo->setFallback('unknown', function () use ($fallback) {
            return $fallback;
        });

        $this->assertSame($fallback, $this->repo->find('unknown'));
        $this->assertEquals('unknown', $fallback->handle());
        $this->assertNull($fallback->namespace());
    }

    /** @test */
    public function it_gets_namespaced_fallback_blueprint()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnNull();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnNull();

        $fallback = $this->repo->make();
        $this->repo->setFallback('foo.bar.unknown', function () use ($fallback) {
            return $fallback;
        });

        $this->assertSame($fallback, $this->repo->find('foo.bar.unknown'));
        $this->assertEquals('unknown', $fallback->handle());
        $this->assertEquals('foo.bar', $fallback->namespace());
    }

    /** @test */
    public function getting_a_non_existent_namespaced_blueprint_will_not_return_the_non_namespaced_fallback()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnNull();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/foo/bar/unknown.yaml')->once()->andReturnNull();

        $fallback = $this->repo->make();
        $this->repo->setFallback('unknown', function () use ($fallback) {
            return $fallback;
        });

        $this->assertNull($this->repo->find('foo.bar.unknown'));
    }

    /** @test */
    public function it_saves_to_disk_in_additional_folder()
    {
        $expectedYaml = <<<'EOT'
title: 'Test Blueprint'
sections:
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

        File::shouldReceive('exists')->with('/path/to/resources/blueprints/the_test_blueprint.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/the_test_blueprint.yaml')->once()->andReturnTrue();
        File::shouldReceive('put')->with('/another/path/to/resources/blueprints/the_test_blueprint.yaml', $expectedYaml)->once();

        $blueprint = (new Blueprint)->setHandle('the_test_blueprint')->setContents([
            'title' => 'Test Blueprint',
            'sections' => [
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

    /** @test */
    public function it_gets_blueprints_in_a_namespace_with_additional_directories()
    {
        $default_dir = '/path/to/resources/blueprints/collections/blog';

        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('exists')->with($default_dir)->once()->andReturnTrue();
        File::shouldReceive('getFilesByType')->with($default_dir, 'yaml')->once()->andReturn(
            new FileCollection([$default_dir.'/first.yaml'])
        );
        File::shouldReceive('get')->with($default_dir.'/first.yaml')->once()->andReturn('title: First Blueprint');

        $additional_dir = '/another/path/to/resources/blueprints/collections/blog';

        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('exists')->with($additional_dir)->once()->andReturnTrue();
        File::shouldReceive('getFilesByType')->with($additional_dir, 'yaml')->once()->andReturn(
            new FileCollection([$additional_dir.'/second.yaml'])
        );
        File::shouldReceive('get')->with($additional_dir.'/second.yaml')->once()->andReturn('title: Second Blueprint');

        $blueprints = $this->repo->in('collections.blog');

        $this->assertInstanceOf(Collection::class, $blueprints);
        $this->assertCount(2, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals(['first', 'second'], $blueprints->keys()->all());
        $this->assertEquals(['first', 'second'], $blueprints->map->handle()->values()->all());
        $this->assertEquals(['collections.blog', 'collections.blog'], $blueprints->map->namespace()->values()->all());
        $this->assertEquals(['First Blueprint', 'Second Blueprint'], $blueprints->map->title()->values()->all());
    }

    /** @test */
    public function it_returns_empty_collection_if_directory_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/test')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/blueprints/test')->once()->andReturnFalse();

        $all = $this->repo->in('test');

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }
}
