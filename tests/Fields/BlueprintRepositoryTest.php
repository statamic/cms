<?php

namespace Tests\Fields;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Factory;
use Statamic\Fields\Field;
use Statamic\Fields\Section;
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
    function it_gets_a_blueprint()
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
        $this->assertEquals([
            'title' => 'Test',
            'sections' => [
                'main' => [
                    'fields' => ['one', 'two']
                ]
            ]
        ], $blueprint->contents());
    }

    /** @test */
    function it_returns_null_if_blueprint_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/path/to/vendor/fallbacks/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    function it_gets_fallback_blueprint()
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
    function it_gets_all_blueprints()
    {
        $firstContents = <<<'EOT'
title: First Blueprint
sections:
  main:
    fields:
      - one
      - two
EOT;

        $secondContents = <<<'EOT'
title: Second Blueprint
sections:
  main:
    fields:
      - two
      - one
EOT;

        $thirdContents = <<<'EOT'
title: Third Blueprint
sections:
  main:
    fields:
      - two
      - one
EOT;

        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('exists')->with('/path/to/resources/blueprints')->once()->andReturnTrue();
        File::shouldReceive('getFilesByTypeRecursively')->with('/path/to/resources/blueprints', 'yaml')->once()->andReturn(new FileCollection([
            '/path/to/resources/blueprints/first.yaml',
            '/path/to/resources/blueprints/second.yaml',
            '/path/to/resources/blueprints/sub/third.yaml',
        ]));
        File::shouldReceive('get')->with('/path/to/resources/blueprints/first.yaml')->once()->andReturn($firstContents);
        File::shouldReceive('get')->with('/path/to/resources/blueprints/second.yaml')->once()->andReturn($secondContents);
        File::shouldReceive('get')->with('/path/to/resources/blueprints/sub/third.yaml')->once()->andReturn($thirdContents);

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(3, $all);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $all);
        $this->assertEquals(['first', 'second', 'sub.third'], $all->keys()->all());
        $this->assertEquals(['first', 'second', 'sub.third'], $all->map->handle()->values()->all());
        $this->assertEquals(['First Blueprint', 'Second Blueprint', 'Third Blueprint'], $all->map->title()->values()->all());
    }

    /** @test */
    function it_returns_empty_collection_if_blueprint_directory_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/blueprints')->once()->andReturnFalse();

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }

    /** @test */
    function it_saves_to_disk()
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
                            ]
                        ],
                        [
                            'handle' => 'bar',
                            'field' => [
                                'type' => 'bar',
                                'display' => 'Bar',
                                'bar' => 'foo',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->repo->save($blueprint);
    }
}
