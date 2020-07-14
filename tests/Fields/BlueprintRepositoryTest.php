<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Support\FileCollection;
use Tests\TestCase;

class BlueprintRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(BlueprintRepository::class)
            ->setDirectory('/path/to/resources/blueprints')
            ->setFallbackDirectory('/path/to/vendor/fallbacks');
    }

    /** @test */
    public function it_gets_a_blueprint()
    {
        $contents = <<<'EOT'
title: Test
sections:
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
            'sections' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    /** @test */
    public function it_gets_a_blueprint_in_a_namespace()
    {
        $contents = <<<'EOT'
title: Test
sections:
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
            'sections' => [
                'main' => [
                    'fields' => ['one', 'two'],
                ],
            ],
        ], $blueprint->contents());
    }

    /** @test */
    public function it_returns_null_if_blueprint_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/path/to/vendor/fallbacks/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    public function it_gets_fallback_blueprint()
    {
        $contents = <<<'EOT'
title: Fallback Blueprint
sections: []
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/test.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/path/to/vendor/fallbacks/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/vendor/fallbacks/test.yaml')->once()->andReturn($contents);

        $blueprint = $this->repo->find('test');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('Fallback Blueprint', $blueprint->title());
    }

    /** @test */
    public function it_saves_to_disk()
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

        File::shouldReceive('exists')->with('/path/to/resources/blueprints')->once()->andReturnFalse();
        File::shouldReceive('makeDirectory')->with('/path/to/resources/blueprints')->once();
        File::shouldReceive('put')->with('/path/to/resources/blueprints/the_test_blueprint.yaml', $expectedYaml)->once();

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
    public function it_gets_blueprints_in_a_namespace()
    {
        $dir = '/path/to/resources/blueprints/collections/blog';
        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('exists')->with($dir)->once()->andReturnTrue();
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

    /** @test */
    public function it_returns_empty_collection_if_directory_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/test')->once()->andReturnFalse();

        $all = $this->repo->in('test');

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }
}
