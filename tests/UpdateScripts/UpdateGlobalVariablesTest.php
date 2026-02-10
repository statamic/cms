<?php

namespace Tests\UpdateScripts;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\YAML;
use Statamic\UpdateScripts\UpdateGlobalVariables;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class UpdateGlobalVariablesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, RunsUpdateScripts;

    protected $globalsPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->globalsPath = $this->fakeStacheDirectory.'/content/globals';

        File::ensureDirectoryExists($this->globalsPath);
    }

    #[Test]
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(UpdateGlobalVariables::class);
    }

    #[Test]
    public function it_migrates_global_variables_in_a_single_site_install()
    {
        File::put($this->globalsPath.'/test.yaml', Yaml::dump([
            'title' => 'Test',
            'data' => [
                'foo' => 'Bar',
            ],
        ]));

        $this->runUpdateScript(UpdateGlobalVariables::class);

        $expected = <<<'YAML'
title: Test

YAML;
        $this->assertEquals($expected, File::get($this->globalsPath.'/test.yaml'));

        $expected = <<<'YAML'
foo: Bar

YAML;
        $this->assertEquals($expected, File::get($this->globalsPath.'/en/test.yaml'));

        unlink($this->globalsPath.'/test.yaml');
        unlink($this->globalsPath.'/en/test.yaml');
    }

    #[Test]
    public function it_builds_the_sites_array_in_a_multi_site_install()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR', 'name' => 'French'],
            'de' => ['url' => '/', 'locale' => 'de_DE', 'name' => 'German'],
        ]);

        File::ensureDirectoryExists($this->globalsPath.'/en');
        File::ensureDirectoryExists($this->globalsPath.'/fr');
        File::ensureDirectoryExists($this->globalsPath.'/de');

        File::put($this->globalsPath.'/test.yaml', Yaml::dump(['title' => 'Test']));
        File::put($this->globalsPath.'/en/test.yaml', Yaml::dump(['foo' => 'Bar', 'baz' => 'Qux']));
        File::put($this->globalsPath.'/fr/test.yaml', Yaml::dump(['origin' => 'en', 'foo' => 'Bar']));
        File::put($this->globalsPath.'/de/test.yaml', Yaml::dump(['origin' => 'fr']));

        $this->runUpdateScript(UpdateGlobalVariables::class);

        // Ensures that the sites array is built correctly.
        $expected = <<<'YAML'
title: Test
sites:
  de: fr
  en: null
  fr: en

YAML;

        $this->assertEquals($expected, File::get($this->globalsPath.'/test.yaml'));

        // Ensure the origin key has been removed from the global variables.
        $this->assertEquals(['foo' => 'Bar', 'baz' => 'Qux'], YAML::parse(File::get($this->globalsPath.'/en/test.yaml')));
        $this->assertEquals(['foo' => 'Bar'], YAML::parse(File::get($this->globalsPath.'/fr/test.yaml')));
        $this->assertEquals([], YAML::parse(File::get($this->globalsPath.'/de/test.yaml')));
    }
}
