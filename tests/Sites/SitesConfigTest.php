<?php

namespace Tests\Sites;

use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Sites\Sites;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SitesConfigTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $yamlPath;

    public function setUp(): void
    {
        parent::setUp();

        File::put($this->yamlPath = base_path('content/sites.yaml'), YAML::dump([
            'english' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
            ],
            'french' => [
                'name' => 'French',
                'locale' => 'fr_FR',
                'url' => '/fr/',
            ],
        ]));

        // Ensure new sites instance in container, so that sites are properly set from new yaml file
        Site::swap(new Sites);
    }

    /** @test */
    public function it_gets_sites_from_yaml()
    {
        $this->assertCount(2, Site::all());

        $this->assertEquals('english', Site::default()->handle());
        $this->assertEquals('English', Site::default()->name());
        $this->assertEquals('en_US', Site::default()->locale());
        $this->assertEquals('en', Site::default()->lang());
        $this->assertEquals('/', Site::default()->url());

        $this->assertEquals('french', Site::get('french')->handle());
        $this->assertEquals('French', Site::get('french')->name());
        $this->assertEquals('fr_FR', Site::get('french')->locale());
        $this->assertEquals('fr', Site::get('french')->lang());
        $this->assertEquals('/fr', Site::get('french')->url());
    }

    /** @test */
    public function it_sets_sites_at_runtime()
    {
        Site::setSites([
            'default' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
            ],
            'arabic' => [
                'name' => 'Arabic (Egypt)',
                'url' => '/ar/',
                'locale' => 'ar_EG',
                'lang' => 'arabic', // testing custom lang string, because it auto-sets off locale too
                'direction' => 'rtl', // by default, `ltr` should be saved
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ]);

        $this->assertCount(2, Site::all());

        $this->assertEquals('default', Site::get('default')->handle());
        $this->assertEquals('English', Site::get('default')->name());
        $this->assertEquals('en_US', Site::get('default')->locale());
        $this->assertEquals('en', Site::get('default')->lang());
        $this->assertEquals('ltr', Site::get('default')->direction());
        $this->assertEquals('/', Site::get('default')->url());
        $this->assertEquals([], Site::get('default')->attributes());

        $this->assertEquals('arabic', Site::get('arabic')->handle());
        $this->assertEquals('Arabic (Egypt)', Site::get('arabic')->name());
        $this->assertEquals('ar_EG', Site::get('arabic')->locale());
        $this->assertEquals('arabic', Site::get('arabic')->lang());
        $this->assertEquals('rtl', Site::get('arabic')->direction());
        $this->assertEquals('/ar', Site::get('arabic')->url());
        $this->assertEquals(['theme' => 'standard'], Site::get('arabic')->attributes());
    }

    /** @test */
    public function it_saves_sites_back_to_yaml()
    {
        Site::setSites([
            'default' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
            ],
            'arabic' => [
                'name' => 'Arabic (Egypt)',
                'url' => '/ar/',
                'locale' => 'ar_EG',
                'lang' => 'arabic', // testing custom lang string, because it auto-sets off locale too
                'direction' => 'rtl', // by default, `ltr` should be saved
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ])->save();

        $expected = [
            'default' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
                'lang' => 'en',
                'direction' => 'ltr',
                'attributes' => [],
            ],
            'arabic' => [
                'name' => 'Arabic (Egypt)',
                'url' => '/ar',
                'locale' => 'ar_EG',
                'lang' => 'arabic',
                'direction' => 'rtl',
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ];

        $this->assertEquals($expected, YAML::file($this->yamlPath)->parse());
    }

    /** @test */
    public function it_saves_single_site_back_to_yaml_in_normalized_sites_array_still()
    {
        Site::setSites([
            'default' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
            ],
        ])->save();

        $expected = [
            'default' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
                'lang' => 'en',
                'direction' => 'ltr',
                'attributes' => [],
            ],
        ];

        $this->assertEquals($expected, YAML::file($this->yamlPath)->parse());
    }
}
