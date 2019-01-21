<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldset;
use Illuminate\Support\Collection;
use Statamic\Fields\FieldRepository;
use Illuminate\Filesystem\Filesystem;
use Statamic\Fields\FieldsetRepository;

class FieldsetRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->repo = app(FieldsetRepository::class)->setDirectory($this->tempDir);
    }

    public function tearDown()
    {
        (new Filesystem)->deleteDirectory($this->tempDir);

        parent::tearDown();
    }

    /** @test */
    function it_gets_a_fieldset()
    {
        $contents = <<<'EOT'
title: Test Fieldset
fields:
  one:
    type: text
    display: First Field
  two:
    type: text
    display: Second Field
EOT;
        file_put_contents($this->tempDir.'/test.yaml', $contents);

        $fieldset = $this->repo->find('test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test Fieldset', $fieldset->title());
        $this->assertEquals('test', $fieldset->handle());
        $this->assertEquals(['one', 'two'], $fieldset->fields()->map->handle()->values()->all());
        $this->assertEquals(['First Field', 'Second Field'], $fieldset->fields()->map->display()->values()->all());
    }

    /** @test */
    function it_gets_a_fieldset_in_a_subdirectory()
    {
        $contents = <<<'EOT'
title: Test Fieldset
fields: []
EOT;
        mkdir($this->tempDir.'/sub');
        file_put_contents($this->tempDir.'/sub/test.yaml', $contents);

        $fieldset = $this->repo->find('sub.test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test Fieldset', $fieldset->title());
        $this->assertEquals('sub.test', $fieldset->handle());

        // Test that using slash delimiter instead of dots returns the same thing.
        $this->assertEquals($fieldset, $this->repo->find('sub/test'));
    }

    /** @test */
    function it_returns_null_if_fieldset_doesnt_exist()
    {
        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    function it_returns_null_if_fieldset_directory_doesnt_exist()
    {
        $this->repo->setDirectory(__DIR__.'/nope');
        $this->assertNull($this->repo->find('unknown'));
    }

    /** @test */
    function it_checks_if_a_fieldset_exists()
    {
        file_put_contents($this->tempDir.'/test.yaml', '');

        $this->assertTrue($this->repo->exists('test'));
        $this->assertFalse($this->repo->exists('unknown'));

        $this->repo->setDirectory(__DIR__.'/nope');
        $this->assertFalse($this->repo->exists('test'));
    }

    /** @test */
    function it_gets_all_fieldsets()
    {
        $firstContents = <<<'EOT'
title: First Fieldset
fields:
  one:
    type: text
    display: First Field
EOT;
        file_put_contents($this->tempDir.'/first.yaml', $firstContents);

        $secondContents = <<<'EOT'
title: Second Fieldset
fields:
  two:
    type: text
    display: Second Field
EOT;
        file_put_contents($this->tempDir.'/second.yaml', $secondContents);

        file_put_contents($this->tempDir.'/not-a-yaml-file.txt', '');

        mkdir($this->tempDir.'/sub');
        $thirdContents = <<<'EOT'
title: Third Fieldset
fields:
  three:
    type: text
    display: Third Field
EOT;
        file_put_contents($this->tempDir.'/sub/third.yaml', $thirdContents);

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(3, $all);
        $this->assertEveryItemIsInstanceOf(Fieldset::class, $all);
        $this->assertEquals(['first', 'second', 'sub.third'], $all->keys()->all());
        $this->assertEquals(['first', 'second', 'sub.third'], $all->map->handle()->values()->all());
        $this->assertEquals(['First Fieldset', 'Second Fieldset', 'Third Fieldset'], $all->map->title()->values()->all());
    }

    /** @test */
    function it_returns_empty_collection_if_fieldset_directory_doesnt_exist()
    {
        $this->repo->setDirectory(__DIR__.'/nope');

        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }

    /** @test */
    function it_saves_to_disk()
    {
        // Set the directory to one that doesn't exist so we can test that the directory would also get created.
        $directory = $this->tempDir . '/doesnt-exist';
        $this->repo->setDirectory($directory);

        $fieldset = (new Fieldset)->setHandle('the_test_fieldset')->setContents([
            'title' => 'Test Fieldset',
            'fields' => [
                'foo' => ['type' => 'textarea', 'bar' => 'baz']
            ]
        ]);

        $this->repo->save($fieldset);

$expectedYaml = <<<'EOT'
title: 'Test Fieldset'
fields:
  foo:
    type: textarea
    bar: baz

EOT;
        $this->assertFileExists($path = $directory.'/the_test_fieldset.yaml');
        $this->assertFileEqualsString($path, $expectedYaml);
        @unlink($path);
    }
}
