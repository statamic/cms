<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Factory;
use Statamic\Fields\Section;
use Statamic\Fields\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Statamic\Fields\BlueprintRepository;

class BlueprintRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->repo = app(BlueprintRepository::class)->setDirectory($this->tempDir);
    }

    public function tearDown()
    {
        (new Filesystem)->deleteDirectory($this->tempDir);
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
        file_put_contents($this->tempDir.'/test.yaml', $contents);

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
        $this->assertNull($this->repo->find('unknown'));
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
        file_put_contents($this->tempDir.'/first.yaml', $firstContents);

        $secondContents = <<<'EOT'
title: Second Blueprint
sections:
  main:
    fields:
      - two
      - one
EOT;
        file_put_contents($this->tempDir.'/second.yaml', $secondContents);

        file_put_contents($this->tempDir.'/not-a-yaml-file.txt', '');

        mkdir($this->tempDir.'/sub');
        $thirdContents = <<<'EOT'
title: Third Blueprint
sections:
  main:
    fields:
      - two
      - one
EOT;
        file_put_contents($this->tempDir.'/sub/third.yaml', $thirdContents);

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(3, $all);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $all);
        $this->assertEquals(['first', 'second', 'sub.third'], $all->keys()->all());
        $this->assertEquals(['first', 'second', 'sub.third'], $all->map->handle()->values()->all());
        $this->assertEquals(['First Blueprint', 'Second Blueprint', 'Third Blueprint'], $all->map->title()->values()->all());
    }
}
