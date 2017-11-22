<?php

namespace Tests\Extend;

use Mockery;
use Tests\TestCase;
use Statamic\API\Addon;
use Statamic\Extend\Management\AddonRepository;
use Illuminate\Contracts\Filesystem\Filesystem;

class AddonRepositoryTest extends TestCase
{
    private $fs;

    public function setUp()
    {
        parent::setUp();

        $this->fs = Mockery::mock(Filesystem::class);
        $this->fs->shouldReceive('disk')->andReturn(Mockery::self());
        $this->instance('filesystem', $this->fs);
    }

    /** @test */
    public function gets_files()
    {
        $files = [
            'site/addons/TestAddon/meta.yaml',
            'statamic/bundles/TestBundle/meta.yaml'
        ];

        $repo = $this->makeRepoWith($files);

        $this->assertEquals($files, $repo->files()->all());
    }

    /** @test */
    public function filters_files_by_filename()
    {
        $files = [
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/resources/assets/js/scripts.js',
            'site/addons/TestAddon/scripts.js',
            'statamic/bundles/TestBundle/meta.yaml'
        ];

        $repo = $this->makeRepoWith($files);

        $expected = [
            'site/addons/TestAddon/resources/assets/js/scripts.js',
            'site/addons/TestAddon/scripts.js',
        ];

        $this->assertEquals($expected, $repo->filename('scripts.js')->files()->all());
    }

    /** @test */
    public function filters_files_by_filename_in_specific_directory()
    {
        $files = [
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/resources/assets/js/scripts.js',
            'site/addons/TestAddon/scripts.js',
            'statamic/bundles/TestBundle/meta.yaml'
        ];

        $repo = $this->makeRepoWith($files);

        $expected = [
            'site/addons/TestAddon/resources/assets/js/scripts.js'
        ];

        $this->assertEquals($expected, $repo->filename('scripts.js', 'resources/assets/js')->files()->all());
        $this->assertEquals($expected, $repo->filename('scripts.js', 'resources/assets/js/')->files()->all());
        $this->assertEquals($expected, $repo->filename('scripts.js', '/resources/assets/js/')->files()->all());
    }

    /** @test */
    public function filters_files_by_filename_regex()
    {
        $files = [
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/resources/assets/js/scripts.js',
            'site/addons/TestAddon/scripts.js',
            'statamic/bundles/TestBundle/meta.yaml'
        ];

        $repo = $this->makeRepoWith($files);

        $expected = [
            'site/addons/TestAddon/resources/assets/js/scripts.js',
            'site/addons/TestAddon/scripts.js',
        ];

        $this->assertEquals($expected, $repo->filenameRegex('/\.js$/')->files()->all());
    }

    /** @test */
    public function gets_addon_class_objects()
    {
        $repo = $this->makeRepoWith([
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/TestAddonTags.php',
            'statamic/bundles/TestBundle/meta.yaml'
        ]);

        $addons = $repo->addons()->all();
        $this->assertEquals(2, count($addons));
        $this->assertFalse($addons[0]->isFirstParty());
        $this->assertTrue($addons[1]->isFirstParty());
    }

    /** @test */
    public function gets_class_names()
    {
        $repo = $this->makeRepoWith([
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/TestAddonTags.php',
            'statamic/bundles/TestBundle/meta.yaml',
            'site/addons/TestBundle/TestBundleTags.php',
        ]);

        $expected = [
            'Statamic\Addons\TestAddon\TestAddonTags',
            'Statamic\Addons\TestBundle\TestBundleTags',
        ];

        $this->assertEquals($expected, $repo->classes()->all());
    }

    /** @test */
    public function it_filters_by_third_party_addons()
    {
        $repo = $this->makeRepoWith([
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/TestAddonTags.php',
            'statamic/bundles/TestBundle/meta.yaml',
            'statamic/bundles/TestBundle/TestBundleTags.php'
        ]);

        $expected = [
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/TestAddonTags.php',
        ];

        $this->assertEquals($expected, $repo->thirdParty()->files()->all());
    }

    /** @test */
    public function it_filters_by_first_party_bundles()
    {
        $repo = $this->makeRepoWith([
            'site/addons/TestAddon/meta.yaml',
            'site/addons/TestAddon/TestAddonTags.php',
            'statamic/bundles/TestBundle/meta.yaml',
            'statamic/bundles/TestBundle/TestBundleTags.php'
        ]);

        $expected = [
            'statamic/bundles/TestBundle/meta.yaml',
            'statamic/bundles/TestBundle/TestBundleTags.php'
        ];

        $this->assertEquals($expected, $repo->firstParty()->files()->all());
    }

    /** @test */
    public function it_gets_commands()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleCommand.php',
            'statamic/bundles/TestBundle/Commands/AnotherCommand.php',
            'site/addons/FooBar/FooBarCommand.php',
            'site/addons/FooBar/Commands/AnotherCommand.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->commands()->files()->all());
    }

    /** @test */
    public function it_gets_apis()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleAPI.php',
            'site/addons/FooBar/FooBarAPI.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->apis()->files()->all());
    }

    /** @test */
    public function it_gets_controllers()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleController.php',
            'site/addons/FooBar/FooBarController.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->controllers()->files()->all());
    }

    /** @test */
    public function it_gets_fieldtypes()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleFieldtype.php',
            'statamic/bundles/TestBundle/Fieldtypes/AnotherFieldtype.php',
            'site/addons/FooBar/FooBarFieldtype.php',
            'site/addons/FooBar/Fieldtypes/AnotherFieldtype.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->fieldtypes()->files()->all());
    }

    /** @test */
    public function it_gets_filters()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleFilter.php',
            'site/addons/FooBar/FooBarFilter.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->filters()->files()->all());
    }

    /** @test */
    public function it_gets_listeners()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleListener.php',
            'site/addons/FooBar/FooBarListener.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->listeners()->files()->all());
    }

    /** @test */
    public function it_gets_modifiers()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleModifier.php',
            'site/addons/FooBar/FooBarModifier.php',
            'site/addons/FooBar/Modifiers/AnotherModifier.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->modifiers()->files()->all());
    }

    /** @test */
    public function it_gets_service_providers()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleServiceProvider.php',
            'site/addons/FooBar/FooBarServiceProvider.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->serviceProviders()->files()->all());
    }

    /** @test */
    public function it_gets_tags()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleTags.php',
            'site/addons/FooBar/FooBarTags.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->tags()->files()->all());
    }

    /** @test */
    public function it_gets_tasks()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleTasks.php',
            'site/addons/FooBar/FooBarTasks.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->tasks()->files()->all());
    }

    /** @test */
    public function it_gets_widgets()
    {
        $expected = [
            'statamic/bundles/TestBundle/TestBundleWidget.php',
            'site/addons/FooBar/FooBarWidget.php',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->widgets()->files()->all());
    }

    /** @test */
    public function it_filters_arbitrary_files()
    {
        $expected = [
            'site/addons/FooBar/composer.json',
        ];

        $this->assertEquals($expected, $this->makePopulatedRepo()->filename('composer.json')->files()->all());
    }

    /** @test */
    public function it_filters_by_installed_addons()
    {
        $this->fs->shouldReceive('exists')->with('site/addons/SimpleAddon/composer.json')->andReturn(false);

        $this->fs->shouldReceive('exists')->with('site/addons/InstalledComposerAddon/composer.json')->andReturn(true);
        $this->fs->shouldReceive('get')->with('site/addons/InstalledComposerAddon/composer.json')->andReturn('{"name":"test/installed"}');

        $this->fs->shouldReceive('exists')->with('site/addons/NotInstalledComposerAddon/composer.json')->andReturn(true);
        $this->fs->shouldReceive('get')->with('site/addons/NotInstalledComposerAddon/composer.json')->andReturn('{"name":"test/not-installed"}');

        $this->fs->shouldReceive('get')->with('statamic/composer.lock')->andReturn('{"packages":[{"name":"test/installed"}]}');

        $repo = $this->makeRepoWith([
            'site/addons/InstalledComposerAddon/composer.json',
            'site/addons/NotInstalledComposerAddon/composer.json',
            'site/addons/SimpleAddon/SimpleAddonTags.php'
        ]);

        $files = $repo->installed()->files()->all();

        $this->assertContains('site/addons/SimpleAddon/SimpleAddonTags.php', $files);
        $this->assertContains('site/addons/InstalledComposerAddon/composer.json', $files);
        $this->assertNotContains('site/addons/NotInstalledComposerAddon/composer.json', $files);
    }

    private function makeRepoWith($files)
    {
        return new AddonRepository(collect_files($files));
    }

    private function makePopulatedRepo()
    {
        $files = [];

        $firstParty = [
            'TestBundle/meta.yaml',
            'TestBundle/TestBundleAPI.php',
            'TestBundle/TestBundleCommand.php',
            'TestBundle/Commands/AnotherCommand.php',
            'TestBundle/TestBundleController.php',
            'TestBundle/TestBundleFieldtype.php',
            'TestBundle/Fieldtypes/AnotherFieldtype.php',
            'TestBundle/TestBundleFilter.php',
            'TestBundle/TestBundleListener.php',
            'TestBundle/TestBundleModifier.php',
            'TestBundle/TestBundleServiceProvider.php',
            'TestBundle/TestBundleTags.php',
            'TestBundle/TestBundleTasks.php',
            'TestBundle/TestBundleWidget.php',
        ];

        $thirdParty = [
            'FooBar/meta.yaml',
            'FooBar/composer.json',
            'FooBar/bootstrap.php',
            'FooBar/FooBarAPI.php',
            'FooBar/FooBarCommand.php',
            'FooBar/Commands/AnotherCommand.php',
            'FooBar/FooBarController.php',
            'FooBar/FooBarFieldtype.php',
            'FooBar/Fieldtypes/AnotherFieldtype.php',
            'FooBar/FooBarFilter.php',
            'FooBar/FooBarListener.php',
            'FooBar/FooBarModifier.php',
            'FooBar/Modifiers/AnotherModifier.php',
            'FooBar/FooBarServiceProvider.php',
            'FooBar/FooBarTags.php',
            'FooBar/FooBarTasks.php',
            'FooBar/FooBarWidget.php',
        ];

        foreach ($firstParty as $file) {
            $files[] = 'statamic/bundles/' . $file;
        }

        foreach ($thirdParty as $file) {
            $files[] = 'site/addons/' . $file;
        }

        return $this->makeRepoWith($files);
    }
}
