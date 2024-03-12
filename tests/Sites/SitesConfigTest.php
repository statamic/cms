<?php

namespace Tests\Sites;

use Statamic\Facades\Config;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\User;
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

        $this->assertSame('english', Site::default()->handle());
        $this->assertSame('English', Site::default()->name());
        $this->assertSame('en_US', Site::default()->locale());
        $this->assertSame('en', Site::default()->lang());
        $this->assertSame('/', Site::default()->url());

        $this->assertSame('french', Site::get('french')->handle());
        $this->assertSame('French', Site::get('french')->name());
        $this->assertSame('fr_FR', Site::get('french')->locale());
        $this->assertSame('fr', Site::get('french')->lang());
        $this->assertSame('/fr', Site::get('french')->url());
    }

    /** @test */
    public function it_gets_default_site_without_yaml()
    {
        File::delete($this->yamlPath);

        // Ensure new sites instance in container,
        // so that it attempts to read non-existent yaml file,
        // and should fall back to default english site
        Site::swap(new Sites);

        $this->assertCount(1, Site::all());

        $this->assertSame('default', Site::default()->handle());
        $this->assertSame(config('app.name'), Site::default()->name());
        $this->assertSame('en_US', Site::default()->locale());
        $this->assertSame('en', Site::default()->lang());
        $this->assertSame('/', Site::default()->url());

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

        $this->assertSame('default', Site::get('default')->handle());
        $this->assertSame('English', Site::get('default')->name());
        $this->assertSame('en_US', Site::get('default')->locale());
        $this->assertSame('en', Site::get('default')->lang());
        $this->assertSame('ltr', Site::get('default')->direction());
        $this->assertSame('/', Site::get('default')->url());
        $this->assertSame([], Site::get('default')->attributes());

        $this->assertSame('arabic', Site::get('arabic')->handle());
        $this->assertSame('Arabic (Egypt)', Site::get('arabic')->name());
        $this->assertSame('ar_EG', Site::get('arabic')->locale());
        $this->assertSame('arabic', Site::get('arabic')->lang());
        $this->assertSame('rtl', Site::get('arabic')->direction());
        $this->assertSame('/ar', Site::get('arabic')->url());
        $this->assertSame(['theme' => 'standard'], Site::get('arabic')->attributes());
    }

    /** @test */
    public function it_saves_single_site_back_to_yaml_in_normalized_sites_array()
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

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    /** @test */
    public function it_saves_multiple_sites_back_to_yaml()
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
                'locale' => 'ar_EG',
                'url' => '/ar',
                'lang' => 'arabic',
                'direction' => 'rtl',
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    /** @test */
    public function it_saves_site_through_cp_endpoint()
    {
        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'name' => 'English',
                'handle' => 'default',
                'locale' => 'en_US',
                'url' => '/',
            ])
            ->assertSuccessful();

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

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    /** @test */
    public function it_saves_multiple_sites_through_cp_endpoint()
    {
        // Multisite requires this config
        Config::set('statamic.sites.enabled', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'sites' => [
                    [
                        'name' => 'English',
                        'handle' => 'default',
                        'locale' => 'en_US',
                        'url' => '/',
                    ],
                    [
                        'name' => 'Arabic (Egypt)',
                        'handle' => 'arabic',
                        'url' => '/ar/',
                        'locale' => 'ar_EG',
                        'lang' => 'arabic', // testing custom lang string, because it auto-sets off locale too
                        'direction' => 'rtl', // by default, `ltr` should be saved
                        'attributes' => [
                            'theme' => 'standard',
                        ],
                    ],
                ],
            ])
            ->assertSuccessful();

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
                'locale' => 'ar_EG',
                'url' => '/ar',
                'lang' => 'arabic',
                'direction' => 'rtl',
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }
}
