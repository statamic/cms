<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\FieldsetNotFoundException;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldsetRepository;
use Statamic\Support\FileCollection;
use Tests\TestCase;

class FieldsetRepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(FieldsetRepository::class)
            ->setDirectory('/path/to/resources/fieldsets');
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_returns_null_if_fieldset_doesnt_exist()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/unknown.yaml')->once()->andReturnFalse();

        $this->assertNull($this->repo->find('unknown'));
    }

    #[Test]
    public function it_checks_if_a_fieldset_exists()
    {
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/unknown.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/path/to/foo/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/foo/unknown.yaml')->once()->andReturnFalse();

        $this->repo->addNamespace('foo', '/path/to/foo');

        $this->assertTrue($this->repo->exists('test'));
        $this->assertFalse($this->repo->exists('unknown'));
        $this->assertTrue($this->repo->exists('foo::test'));
        $this->assertFalse($this->repo->exists('foo::unknown'));
        $this->assertFalse($this->repo->exists('unknownnamespace::test'));
        $this->assertFalse($this->repo->exists('unknownnamespace::unknown'));
    }

    #[Test]
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
        $firstNamespacedContents = <<<'EOT'
title: First Namespaced Fieldset
fields:
  three:
    type: text
    display: Third Field
EOT;
        $secondNamespacedContents = <<<'EOT'
title: Second Namespaced Fieldset
fields:
  three:
    type: text
    display: Fourth Field
EOT;
        $firstOverriddenNamespacedContents = <<<'EOT'
title: First Namespaced Fieldset (vendor override)
fields:
  three:
    type: text
    display: Third Field
EOT;

        File::shouldReceive('withAbsolutePaths')->times(2)->andReturnSelf();
        File::shouldReceive('getFilesByTypeRecursively')->with('/path/to/foo', 'yaml')->once()->andReturn(new FileCollection([
            '/path/to/foo/bar/first.yaml',
            '/path/to/foo/bar/second.yaml',
        ]));
        File::shouldReceive('getFilesByTypeRecursively')->with('/path/to/resources/fieldsets', 'yaml')->once()->andReturn(new FileCollection([
            '/path/to/resources/fieldsets/first.yaml',
            '/path/to/resources/fieldsets/second.yaml',
            '/path/to/resources/fieldsets/sub/third.yaml',
            '/path/to/resources/fieldsets/vendor/foo/bar/first.yaml',
        ]));
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/first.yaml')->once()->andReturn($firstContents);
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/second.yaml')->once()->andReturn($secondContents);
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/sub/third.yaml')->once()->andReturn($thirdContents);
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/vendor/foo/bar/first.yaml')->once()->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/vendor/foo/bar/second.yaml')->once()->andReturnFalse();
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/vendor/foo/bar/first.yaml')->once()->andReturn($firstOverriddenNamespacedContents);
        File::shouldReceive('get')->with('/path/to/foo/bar/second.yaml')->once()->andReturn($secondNamespacedContents);

        $this->repo->addNamespace('foo', '/path/to/foo');
        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(5, $all);
        $this->assertEveryItemIsInstanceOf(Fieldset::class, $all);
        $this->assertEquals(['first', 'second', 'sub.third', 'foo::bar.first', 'foo::bar.second'], $all->keys()->all());
        $this->assertEquals(['first', 'second', 'sub.third', 'foo::bar.first', 'foo::bar.second'], $all->map->handle()->values()->all());
        $this->assertEquals(['First Fieldset', 'Second Fieldset', 'Third Fieldset', 'First Namespaced Fieldset (vendor override)', 'Second Namespaced Fieldset'], $all->map->title()->values()->all());
    }

    #[Test]
    public function it_returns_empty_collection_if_fieldset_directory_doesnt_exist()
    {
        $all = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount(0, $all);
    }

    #[Test]
    #[DataProvider('saveProvider')]
    public function it_saves_to_disk($handle, $expectedPath)
    {
        Facades\Fieldset::addNamespace('foo', '/path/to/foo');

        $expectedYaml = <<<'EOT'
title: 'Test Fieldset'
fields:
  -
    handle: foo
    field:
      type: textarea
      bar: baz

EOT;

        File::shouldReceive('put')->with($expectedPath, $expectedYaml)->once();

        $fieldset = (new Fieldset)
            ->setHandle($handle)
            ->setContents([
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

    public static function saveProvider()
    {
        return [
            'standard' => ['test', '/path/to/resources/fieldsets/test.yaml'],
            'standard subdir' => ['subdir.test', '/path/to/resources/fieldsets/subdir/test.yaml'],
            'namespace' => ['foo::test', '/path/to/resources/fieldsets/vendor/foo/test.yaml'],
            'namespace, subdir' => ['foo::subdir.test', '/path/to/resources/fieldsets/vendor/foo/subdir/test.yaml'],
        ];
    }

    #[Test]
    public function it_deletes_a_fieldset()
    {
        File::shouldReceive('delete')->with('/path/to/resources/fieldsets/test.yaml')->once();

        $fieldset = (new Fieldset)->setHandle('test');

        $this->repo->delete($fieldset);
    }

    #[Test]
    public function it_doesnt_delete_namespaced_fieldsets()
    {
        $this->expectExceptionMessage('Namespaced fieldsets cannot be deleted');

        File::shouldReceive('delete')->never();

        $fieldset = (new Fieldset)->setHandle('foo::test');

        $this->repo->delete($fieldset);
    }

    #[Test]
    public function it_resets_a_namespaced_fieldset()
    {
        File::shouldReceive('delete')->once();

        $fieldset = (new Fieldset)->setHandle('foo::test');

        $this->repo->reset($fieldset);
    }

    #[Test]
    public function it_gets_a_namespaced_fieldset()
    {
        $contents = <<<'EOT'
title: Test Fieldset
fields: []
EOT;
        $this->repo->addNamespace('foo', '/path/to/foo');

        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/vendor/foo/bar/baz/test.yaml')->once()->andReturnFalse();
        File::shouldReceive('exists')->with('/path/to/foo/bar/baz/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/foo/bar/baz/test.yaml')->once()->andReturn($contents);

        $fieldset = $this->repo->find('foo::bar.baz.test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test Fieldset', $fieldset->title());
        $this->assertEquals('foo::bar.baz.test', $fieldset->handle());
    }

    #[Test]
    public function it_gets_an_overridden_namespaced_fieldset()
    {
        $contents = <<<'EOT'
title: Overridden Fieldset
fields: []
EOT;
        $this->repo->addNamespace('foo', '/path/to/foo');

        File::shouldReceive('exists')->with('/path/to/resources/fieldsets/vendor/foo/bar/baz/test.yaml')->once()->andReturnTrue();
        File::shouldReceive('get')->with('/path/to/resources/fieldsets/vendor/foo/bar/baz/test.yaml')->once()->andReturn($contents);

        $fieldset = $this->repo->find('foo::bar.baz.test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Overridden Fieldset', $fieldset->title());
        $this->assertEquals('foo::bar.baz.test', $fieldset->handle());

        $this->assertNull($this->repo->find('vendor.foo.bar.baz.test'));
        $this->assertFalse($this->repo->exists('vendor.foo.bar.baz.test'));
    }

    #[Test]
    public function it_gets_a_blueprint_using_find_or_fail()
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

        $fieldset = $this->repo->findOrFail('test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('test', $fieldset->handle());
    }

    #[Test]
    public function find_or_fail_throws_exception_when_blueprint_does_not_exist()
    {
        $this->expectException(FieldsetNotFoundException::class);
        $this->expectExceptionMessage('Fieldset [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }
}
