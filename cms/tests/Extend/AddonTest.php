<?php

namespace Statamic\Testing\Extend;

use Mockery;
use Tests\TestCase;
use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\Extend\Meta;
use Statamic\Extend\Addon;
use Statamic\Config\Addons;
use Illuminate\Contracts\Filesystem\Filesystem;

class AddonTest extends TestCase
{
    private $fs;

    /** @var Addon */
    private $addon;

    public function setUp()
    {
        parent::setUp();

        $this->addon = new Addon('TestAddon');

        $this->fs = Mockery::mock(Filesystem::class);
        $this->fs->shouldReceive('disk')->andReturn(Mockery::self());
        $this->instance('filesystem', $this->fs);
    }

    /** @test */
    public function it_gets_the_id()
    {
        $this->assertEquals('TestAddon', $this->addon->id());
    }

    /** @test */
    public function it_initializes_the_id_as_studly_case()
    {
        $addon = new Addon('test addon');
        $this->assertEquals('TestAddon', $addon->id());
    }

    /** @test */
    public function it_gets_the_handle()
    {
        $this->assertEquals('test_addon', $this->addon->handle());
    }

    /** @test */
    public function it_gets_the_slug()
    {
        $this->assertEquals('test-addon', $this->addon->slug());
    }

    /** @test */
    public function it_determines_whether_its_first_party()
    {
        $this->assertFalse($this->addon->isFirstParty());
    }

    /** @test */
    public function it_sets_whether_its_first_party()
    {
        $returned = $this->addon->isFirstParty(true);

        $this->assertTrue($this->addon->isFirstParty());
        $this->assertInstanceOf(Addon::class, $returned);
    }

    /** @test */
    public function it_gets_the_directory()
    {
        $this->assertEquals(
            Path::resolve(Path::makeFull('site/addons/TestAddon')),
            Path::resolve($this->addon->directory())
        );
    }

    /** @test */
    public function it_gets_the_directory_for_first_party_addons()
    {
        $this->addon->isFirstParty(true);

        $this->assertEquals(
            Path::resolve(Path::makeFull('statamic/bundles/TestAddon')),
            Path::resolve($this->addon->directory())
        );
    }

    /** @test */
    public function it_gets_the_settings_url()
    {
        $this->assertEquals(
            '/cp/addons/test-addon/settings',
            URL::makeRelative($this->addon->settingsUrl())
        );
    }

    /** @test */
    public function it_checks_if_a_file_exists()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/test.txt')->andReturn(true);
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/notfound.txt')->andReturn(false);

        $this->assertTrue($this->addon->hasFile('test.txt'));
        $this->assertFalse($this->addon->hasFile('notfound.txt'));
    }

    /** @test */
    public function it_gets_file_contents()
    {
        $this->fs->shouldReceive('get')->with('site/addons/TestAddon/test.txt')->andReturn('the file contents');

        $this->assertEquals('the file contents', $this->addon->getFile('test.txt'));
    }

    /** @test */
    public function it_writes_file_contents()
    {
        $this->fs->shouldReceive('put')->with('site/addons/TestAddon/test.txt', 'the file contents');

        $this->addon->putFile('test.txt', 'the file contents');
    }

    /** @test */
    public function it_gets_the_name_from_the_meta_file_if_it_exists()
    {
        $this->withMeta('name: My Test Addon');

        $this->assertEquals('My Test Addon', $this->addon->name());
    }

    /** @test */
    public function it_gets_the_name_from_id_if_the_meta_doesnt_exist()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/meta.yaml')->andReturn(false);

        $this->assertEquals('TestAddon', $this->addon->name());
    }

    /** @test */
    public function it_gets_the_url()
    {
        $this->withMeta('url: http://example.com');

        $this->assertEquals('http://example.com', $this->addon->url());
    }

    /** @test */
    public function it_gets_the_version()
    {
        $this->withMeta('version: 1.0');

        $this->assertEquals('1.0', $this->addon->version());
    }

    /** @test */
    public function it_gets_the_developer_name()
    {
        $this->withMeta('developer: Testerson');

        $this->assertEquals('Testerson', $this->addon->developer());
    }

    /** @test */
    public function it_gets_the_developer_url()
    {
        $this->withMeta('developer_url: http://testerson.com');

        $this->assertEquals('http://testerson.com', $this->addon->developerUrl());
    }

    /** @test */
    public function it_gets_the_description()
    {
        $this->withMeta('description: This is an addon.');

        $this->assertEquals('This is an addon.', $this->addon->description());
    }

    /** @test */
    public function its_commercial_if_it_says_so_in_the_meta()
    {
        $this->withMeta('commercial: true');

        $this->assertTrue($this->addon->isCommercial());
    }

    /** @test */
    public function its_not_commercial_if_the_meta_doesnt_contain_a_commercial_boolean()
    {
        $this->withMeta(''); // no mention of commercial

        $this->assertFalse($this->addon->isCommercial());
    }

    /** @test */
    public function its_not_commercial_if_there_is_no_meta_file()
    {
        $this->withoutMeta();

        $this->assertFalse($this->addon->isCommercial());
    }

    /** @test */
    public function it_gets_the_license_key()
    {
        $addons = new Addons;
        $addons->hydrate(['test_addon' => ['license_key' => 'TESTLICENSEKEY']]);
        $this->instance(Addons::class, $addons);

        $this->assertEquals('TESTLICENSEKEY', $this->addon->licenseKey());

        $addons->hydrate(['test_addon' => []]);

        $this->assertNull($this->addon->licenseKey());
    }

    /** @test */
    public function it_should_have_settings_if_the_file_exists()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/settings.yaml')->andReturn(true);

        $this->assertTrue($this->addon->hasSettings());
    }

    /** @test */
    public function it_should_have_settings_if_the_addon_is_commercial()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/settings.yaml')->andReturn(false);
        $this->withMeta('commercial: true');

        $this->assertTrue($this->addon->hasSettings());
    }

    /** @test */
    public function it_makes_an_empty_meta_object()
    {
        $this->assertInstanceOf(Meta::class, $this->addon->makeMeta());
    }

    /** @test */
    public function it_makes_a_meta_object_and_populates_it()
    {
        $meta = $this->addon->makeMeta(['foo' => 'bar']);

        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertEquals('bar', $meta->get('foo'));
    }

    /** @test */
    public function it_gets_the_loaded_meta_object()
    {
        $this->withMeta();
        $meta = $this->addon->meta();

        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertTrue($meta->isLoaded());
    }

    /** @test */
    public function it_is_installed_if_no_composer_file_exists()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/composer.json')->andReturn(false);

        $this->assertTrue($this->addon->isInstalled());
    }

    /** @test */
    public function it_is_installed_if_composer_lock_file_contains_package()
    {
        $path = 'site/addons/TestAddon/composer.json';
        $this->fs->shouldReceive('exists')->with($path)->andReturn(true);
        $this->fs->shouldReceive('get')->with($path)->andReturn(json_encode(['name' => 'test/test-addon']));
        $this->fs->shouldReceive('get')->with('statamic/composer.lock')->andReturn(json_encode([
            'packages' => [['name' => 'test/test-addon']]
        ]));

        $this->assertTrue($this->addon->isInstalled());
    }

    /** @test */
    public function it_is_not_installed_if_composer_lock_file_doesnt_contains_package()
    {
        $path = 'site/addons/TestAddon/composer.json';
        $this->fs->shouldReceive('exists')->with($path)->andReturn(true);
        $this->fs->shouldReceive('get')->with($path)->andReturn(json_encode(['name' => 'test/test-addon']));
        $this->fs->shouldReceive('get')->with('statamic/composer.lock')->andReturn(json_encode([
            'packages' => [['name' => 'something/else']]
        ]));

        $this->assertFalse($this->addon->isInstalled());
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $this->fs->shouldReceive('deleteDirectory')->with('site/addons/TestAddon');

        $this->addon->delete();
    }

    private function withoutMeta()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/meta.yaml')->andReturn(false);
    }

    private function withMeta($contents = '')
    {
        $this->fs->shouldReceive('exists')->with('site/addons/TestAddon/meta.yaml')->andReturn(true);
        $this->fs->shouldReceive('get')->with('site/addons/TestAddon/meta.yaml')->andReturn($contents);
    }
}
