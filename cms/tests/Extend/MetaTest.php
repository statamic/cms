<?php

namespace Statamic\Testing\Extend;

use Mockery;
use Statamic\Extend\Addon;
use Statamic\Extend\Meta;
use Statamic\Testing\TestCase;
use Illuminate\Contracts\Filesystem\Filesystem;

class MetaTest extends TestCase
{
    private $fs;

    /** @var Meta */
    private $meta;

    public function setUp()
    {
        parent::setUp();

        $this->meta = new Meta(new Addon('TestAddon'));

        $this->fs = Mockery::mock(Filesystem::class);
        $this->fs->shouldReceive('disk')->andReturn(Mockery::self());
        $this->instance('filesystem', $this->fs);
    }

    /** @test */
    public function it_sets_and_gets_the_data()
    {
        $this->assertEquals([], $this->meta->data());

        $this->meta->data(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->meta->data());
    }

    /** @test */
    public function it_gets_and_sets_keys_on_the_data()
    {
        $this->assertNull($this->meta->get('baz'));
        $this->assertEquals('fallback', $this->meta->get('baz', 'fallback'));

        $this->meta->set('baz', 'qux');

        $this->assertEquals('qux', $this->meta->get('baz'));
    }

    /** @test */
    public function it_checks_if_data_has_key()
    {
        $this->assertFalse($this->meta->has('foo'));

        $this->meta->set('foo', 'bar');

        $this->assertTrue($this->meta->has('foo'));
    }

    /** @test */
    public function it_checks_if_the_file_exists()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/meta.yaml')->andReturn(true);

        $this->assertTrue($this->meta->exists());
    }

    /** @test */
    public function it_checks_if_the_file_doesnt_exist()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/meta.yaml')->andReturn(false);

        $this->assertFalse($this->meta->exists());
    }

    /** @test */
    public function it_loads_the_contents_from_disk_if_there_is_one()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/meta.yaml')->andReturn(true);
        $this->fs->shouldReceive('get')->with('site/addons/TestAddon/meta.yaml')->andReturn('foo: bar');

        $this->assertFalse($this->meta->isLoaded());
        $this->assertEquals([], $this->meta->data());

        $this->meta->load();

        $this->assertTrue($this->meta->isLoaded());
        $this->assertEquals(['foo' => 'bar'], $this->meta->data());
    }

    /** @test */
    public function it_doesnt_load_the_contents_from_disk_if_there_isnt_one()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/meta.yaml')->andReturn(false);

        $this->assertFalse($this->meta->isLoaded());

        $this->meta->load();

        $this->assertTrue($this->meta->isLoaded());
        $this->assertEquals([], $this->meta->data());
    }

    /** @test */
    public function it_saves_the_data_to_file()
    {
        $this->fs->shouldReceive('put')->with(
            'site/addons/TestAddon/meta.yaml',
            "one: two\nfoo: bar\nbaz: qux\n"
        );

        $this->meta->data(['one' => 'two', 'foo' => 'bar', 'baz' => 'qux'])->save();
    }
}
