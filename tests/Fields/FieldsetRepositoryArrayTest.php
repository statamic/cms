<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldsetRepository;
use Statamic\Support\FileCollection;
use Tests\TestCase;

class FieldsetRepositoryArrayTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(FieldsetRepository::class)
            ->setDirectory([
                '/path/to/resources/fieldsets',
                '/another/path/to/resources/fieldsets',
            ]);
    }

    /** @test */
    public function it_still_accepts_string_for_directory()
    {
        $this->repo->setDirectory('test');

        $this->assertEquals('test', $this->repo->directory());
    }

    /** @test */
    public function it_returns_first_directory_as_default()
    {
        $this->assertEquals('/path/to/resources/fieldsets', $this->repo->directory());
    }

    /** @test */
    public function it_returns_all_directories()
    {
        $this->assertEquals([
            '/path/to/resources/fieldsets',
            '/another/path/to/resources/fieldsets',
        ], $this->repo->directories());
    }

    /** @test */
    public function it_gets_a_fieldset_from_default_directory()
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
    public function it_gets_a_fieldset_from_second_directory()
    {
        $contents = <<<'EOT'
title: Test External Fieldset
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
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/another/path/to/resources/fieldsets/test.yaml')->once()->andReturn($contents);

        $fieldset = $this->repo->find('test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test External Fieldset', $fieldset->title());
        $this->assertEquals('test', $fieldset->handle());
        $this->assertEquals(['one', 'two'], $fieldset->fields()->all()->map->handle()->values()->all());
        $this->assertEquals(['First Field', 'Second Field'], $fieldset->fields()->all()->map->display()->values()->all());
    }

    /** @test */
    public function it_gets_a_fieldset_path_from_default_directory()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturnTrue();

        $path = $this->repo->path('test');

        $this->assertEquals('/path/to/resources/fieldsets/test.yaml', $path);
    }

    /** @test */
    public function it_gets_a_fieldset_path_from_second_directory()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/test.yaml')->once()->andReturnTrue();

        $path = $this->repo->path('test');

        $this->assertEquals('/another/path/to/resources/fieldsets/test.yaml', $path);
    }

    /** @test */
    public function it_fieldset_path_returns_null_if_missing()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/missing.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/missing.yaml')->once()->andReturnFalse();

        $path = $this->repo->path('missing');

        $this->assertNull($path);
    }

    /** @test */
    public function it_gets_a_fieldset_in_first_subdirectory()
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
    public function it_gets_a_fieldset_in_second_subdirectory()
    {
        $contents = <<<'EOT'
title: Test Fieldset
fields: []
EOT;
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/sub/test.yaml')->twice()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/sub/test.yaml')->twice()->andReturnTrue();
        File::shouldReceive('get')->with('/another/path/to/resources/fieldsets/sub/test.yaml')->twice()->andReturn($contents);

        $fieldset = $this->repo->find('sub.test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test Fieldset', $fieldset->title());
        $this->assertEquals('sub.test', $fieldset->handle());

        // Test that using slash delimiter instead of dots returns the same thing.
        $this->assertEquals($fieldset, $this->repo->find('sub/test'));
    }

    /** @test */
    public function it_returns_null_if_path_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/missing.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/missing.yaml')->once()->andReturnFalse();

        $path = $this->repo->path('missing');

        $this->assertNull($path);
    }

    /** @test */
    public function it_checks_if_a_fieldset_exists()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/unknown.yaml')->once()->andReturnFalse();

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
        $fourthContents = <<<'EOT'
title: Fourth Fieldset
fields:
  three:
    type: text
    display: Fourth Field
EOT;

        File::shouldReceive('withAbsolutePaths')->twice()->andReturnSelf();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets')->once()->andReturnTrue();
        File::shouldReceive('getFilesByTypeRecursively')->with('/path/to/resources/fieldsets', 'yaml')->once()->andReturn(new FileCollection([
            '/path/to/resources/fieldsets/first.yaml',
            '/path/to/resources/fieldsets/second.yaml',
        ]));
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/first.yaml')->once()->andReturn($firstContents);
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/second.yaml')->once()->andReturn($secondContents);
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets')->once()->andReturnTrue();
        File::shouldReceive('getFilesByTypeRecursively')->with('/another/path/to/resources/fieldsets', 'yaml')->once()->andReturn(new FileCollection([
            '/another/path/to/resources/fieldsets/sub/third.yaml',
            '/another/path/to/resources/fieldsets/fourth.yaml',
        ]));
        File::shouldReceive('get')->with('/another/path/to/resources/fieldsets/sub/third.yaml')->once()->andReturn($thirdContents);
        File::shouldReceive('get')->with('/another/path/to/resources/fieldsets/fourth.yaml')->once()->andReturn($fourthContents);

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(4, $all);
        $this->assertEveryItemIsInstanceOf(Fieldset::class, $all);
        $this->assertEquals(['first', 'second', 'sub.third', 'fourth'], $all->keys()->all());
        $this->assertEquals(['first', 'second', 'sub.third', 'fourth'], $all->map->handle()->values()->all());
        $this->assertEquals(['First Fieldset', 'Second Fieldset', 'Third Fieldset', 'Fourth Fieldset'], $all->map->title()->values()->all());
    }

    /** @test */
    public function it_returns_first_fieldset_when_duplicates_exists()
    {
        $duplicateContents = <<<'EOT'
title: Duplicate Fieldset
fields:
  one:
    type: text
    display: Duplicate Field
EOT;
        $originalContents = <<<'EOT'
title: Original Fieldset
fields:
  two:
    type: text
    display: Original Field
EOT;

        File::shouldReceive('withAbsolutePaths')->twice()->andReturnSelf();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets')->once()->andReturnTrue();
        File::shouldReceive('getFilesByTypeRecursively')->with('/path/to/resources/fieldsets', 'yaml')->once()->andReturn(new FileCollection([
            '/path/to/resources/fieldsets/test.yaml',
        ]));
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturn($duplicateContents);
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets')->once()->andReturnTrue();
        File::shouldReceive('getFilesByTypeRecursively')->with('/another/path/to/resources/fieldsets', 'yaml')->once()->andReturn(new FileCollection([
            '/another/path/to/resources/fieldsets/test.yaml',
        ]));
        File::shouldReceive('get')->with('/another/path/to/resources/fieldsets/test.yaml')->once()->andReturn($originalContents);

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(1, $all);
        $this->assertEveryItemIsInstanceOf(Fieldset::class, $all);
        $this->assertEquals(['test'], $all->keys()->all());
        $this->assertEquals(['test'], $all->map->handle()->values()->all());
        $this->assertEquals(['Duplicate Fieldset'], $all->map->title()->values()->all());
    }

    /** @test */
    public function it_returns_empty_collection_if_fieldset_directory_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets')->once()->andReturnFalse();

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }

    /** @test */
    public function it_saves_to_disk_using_first_default_folder()
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
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/the_test_fieldset.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/the_test_fieldset.yaml')->once()->andReturnFalse();
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

    /** @test */
    public function it_saves_to_disk_in_second_folder()
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
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/the_test_fieldset.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/the_test_fieldset.yaml')->once()->andReturnTrue();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets')->once()->andReturnTrue();
        File::shouldReceive('put')->with('/another/path/to/resources/fieldsets/the_test_fieldset.yaml', $expectedYaml)->once();

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

    /** @test */
    public function it_deletes_from_disk_in_second_folder()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/the_test_fieldset.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/another/path/to/resources/fieldsets/the_test_fieldset.yaml')->once()->andReturnTrue();
        File::shouldReceive('delete')->with('/another/path/to/resources/fieldsets/the_test_fieldset.yaml')->once();

        $fieldset = (new Fieldset)->setHandle('the_test_fieldset')->setContents([
            'title' => 'Test Fieldset',
            'fields' => [
                [
                    'handle' => 'foo',
                    'field' => ['type' => 'textarea', 'bar' => 'baz'],
                ],
            ],
        ]);

        $this->repo->delete($fieldset);
    }
}
