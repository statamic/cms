<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldsetRepository;
use Statamic\Support\FileCollection;
use Tests\TestCase;

class FieldsetRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(FieldsetRepository::class)
            ->setDirectory('/path/to/resources/fieldsets');
    }

    /** @test */
    public function it_gets_a_fieldset()
    {
        $contents = <<<'EOT'
title: Test Fieldset
fields:
  -
    handle: one
    field:
      type: text
      display: First Field
  -
    handle: two
    field:
      type: text
      display: Second Field
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturn($contents);

        $fieldset = $this->repo->find('test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test Fieldset', $fieldset->title());
        $this->assertEquals('test', $fieldset->handle());
        $this->assertEquals(['one', 'two'], $fieldset->fields()->all()->map->handle()->values()->all());
        $this->assertEquals(['First Field', 'Second Field'], $fieldset->fields()->all()->map->display()->values()->all());
    }

    /** @test */
    public function it_gets_a_fieldset_in_a_subdirectory()
    {
        $contents = <<<'EOT'
title: Test Fieldset
fields: []
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/sub/test.yaml')->twice()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/sub/test.yaml')->twice()->andReturn($contents);

        $fieldset = $this->repo->find('sub.test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test Fieldset', $fieldset->title());
        $this->assertEquals('sub.test', $fieldset->handle());

        // Test that using slash delimiter instead of dots returns the same thing.
        $this->assertEquals($fieldset, $this->repo->find('sub/test'));
    }

    /** @test */
    public function it_returns_null_if_fieldset_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    public function it_checks_if_a_fieldset_exists()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/unknown.yaml')->once()->andReturnFalse();

        $this->assertTrue($this->repo->exists('test'));
        $this->assertFalse($this->repo->exists('unknown'));
    }

    /** @test */
    public function it_gets_all_fieldsets()
    {
        $firstContents = <<<'EOT'
title: First Fieldset
fields:
  one:
    type: text
    display: First Field
EOT;
        $secondContents = <<<'EOT'
title: Second Fieldset
fields:
  two:
    type: text
    display: Second Field
EOT;
        $thirdContents = <<<'EOT'
title: Third Fieldset
fields:
  three:
    type: text
    display: Third Field
EOT;

        File::shouldReceive('withAbsolutePaths')->once()->andReturnSelf();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets')->once()->andReturnTrue();
        File::shouldReceive('getFilesByTypeRecursively')->with('/path/to/resources/fieldsets', 'yaml')->once()->andReturn(new FileCollection([
            '/path/to/resources/fieldsets/first.yaml',
            '/path/to/resources/fieldsets/second.yaml',
            '/path/to/resources/fieldsets/sub/third.yaml',
        ]));
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/first.yaml')->once()->andReturn($firstContents);
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/second.yaml')->once()->andReturn($secondContents);
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/sub/third.yaml')->once()->andReturn($thirdContents);

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(3, $all);
        $this->assertEveryItemIsInstanceOf(Fieldset::class, $all);
        $this->assertEquals(['first', 'second', 'sub.third'], $all->keys()->all());
        $this->assertEquals(['first', 'second', 'sub.third'], $all->map->handle()->values()->all());
        $this->assertEquals(['First Fieldset', 'Second Fieldset', 'Third Fieldset'], $all->map->title()->values()->all());
    }

    /** @test */
    public function it_returns_empty_collection_if_fieldset_directory_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets')->once()->andReturnFalse();

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }

    /** @test */
    public function it_saves_to_disk()
    {
        $expectedYaml = <<<'EOT'
title: 'Test Fieldset'
fields:
  -
    handle: foo
    field:
      type: textarea
      bar: baz

EOT;
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets')->once()->andReturnFalse();
        File::shouldReceive('makeDirectory')->with('/path/to/resources/fieldsets')->once();
        File::shouldReceive('put')->with('/path/to/resources/fieldsets/the_test_fieldset.yaml', $expectedYaml)->once();

        $fieldset = (new Fieldset)->setHandle('the_test_fieldset')->setContents([
            'title' => 'Test Fieldset',
            'fields' => [
                [
                    'handle' => 'foo',
                    'field' => ['type' => 'textarea', 'bar' => 'baz'],
                ],
            ],
        ]);

        $this->repo->save($fieldset);
    }
}
