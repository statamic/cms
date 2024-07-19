<?php

namespace Tests\Sites;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SiteCreated;
use Statamic\Events\SiteDeleted;
use Statamic\Events\SiteSaved;
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

        File::put($this->yamlPath = resource_path('sites.yaml'), YAML::dump([
            'english' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
            ],
            'french' => [
                'name' => 'French',
                'url' => '/fr/',
                'locale' => 'fr_FR',
            ],
        ]));

        // Ensure new sites instance in container, so that sites are properly set from new yaml file
        Site::swap(new Sites);
    }

    #[Test]
    public function it_gets_sites_from_yaml()
    {
        $this->assertCount(2, Site::all());

        $this->assertSame('english', Site::default()->handle());
        $this->assertSame('English', Site::default()->name());
        $this->assertSame('/', Site::default()->url());
        $this->assertSame('en_US', Site::default()->locale());
        $this->assertSame('en', Site::default()->lang());

        $this->assertSame('french', Site::get('french')->handle());
        $this->assertSame('French', Site::get('french')->name());
        $this->assertSame('/fr', Site::get('french')->url());
        $this->assertSame('fr_FR', Site::get('french')->locale());
        $this->assertSame('fr', Site::get('french')->lang());
    }

    #[Test]
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
        $this->assertSame('/', Site::default()->url());
        $this->assertSame('en_US', Site::default()->locale());
        $this->assertSame('en', Site::default()->lang());
    }

    #[Test]
    public function it_sets_sites_at_runtime()
    {
        Site::setSites([
            'default' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
                'lang' => 'slang', // testing custom lang string, because it auto-sets itself off locale
            ],
            'arabic' => [
                'name' => 'Arabic (Egypt)',
                'url' => '/ar/',
                'locale' => 'ar_EG',
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ]);

        $this->assertCount(2, Site::all());

        $this->assertSame('default', Site::get('default')->handle());
        $this->assertSame('English', Site::get('default')->name());
        $this->assertSame('/', Site::get('default')->url());
        $this->assertSame('en_US', Site::get('default')->locale());
        $this->assertSame('slang', Site::get('default')->lang());
        $this->assertSame('ltr', Site::get('default')->direction());
        $this->assertSame([], Site::get('default')->attributes());

        $this->assertSame('arabic', Site::get('arabic')->handle());
        $this->assertSame('Arabic (Egypt)', Site::get('arabic')->name());
        $this->assertSame('/ar', Site::get('arabic')->url());
        $this->assertSame('ar_EG', Site::get('arabic')->locale());
        $this->assertSame('ar', Site::get('arabic')->lang());
        $this->assertSame('rtl', Site::get('arabic')->direction());
        $this->assertSame(['theme' => 'standard'], Site::get('arabic')->attributes());
    }

    #[Test]
    public function it_resolves_antlers_when_resolving_sites()
    {
        Config::set('app', [
            'name' => 'English Resolved',
            'url' => '/resolved',
            'faker_locale' => 'xx_XX',
            'locale' => 'xx',
        ]);

        Config::set('statamic.some_addon.theme', 'sunset');

        Site::setSites([
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
                'lang' => '{{ config:app:locale }}',
                'attributes' => [
                    'theme' => '{{ config:statamic:some_addon:theme }}',
                ],
            ],
        ]);

        $this->assertSame('default', Site::default()->handle());
        $this->assertSame('English Resolved', Site::default()->name());
        $this->assertSame('/resolved', Site::default()->url());
        $this->assertSame('xx_XX', Site::default()->locale());
        $this->assertSame('xx', Site::default()->lang());
        $this->assertSame(['theme' => 'sunset'], Site::default()->attributes());
    }

    #[Test]
    public function it_saves_single_site_back_to_yaml_in_normalized_sites_array()
    {
        Site::setSites([
            'default' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
            ],
        ])->save();

        $expected = [
            'default' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    #[Test]
    public function it_saves_multiple_sites_back_to_yaml()
    {
        Site::setSites([
            'default' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
            ],
            'arabic' => [
                'name' => 'Arabic (Egypt)',
                'url' => '/ar/',
                'locale' => 'ar_EG',
                'lang' => 'arabic', // testing custom lang string, because it auto-sets itself off locale
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ])->save();

        $expected = [
            'default' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
            ],
            'arabic' => [
                'name' => 'Arabic (Egypt)',
                'url' => '/ar/',
                'locale' => 'ar_EG',
                'lang' => 'arabic',
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    #[Test]
    public function it_saves_single_site_back_to_yaml_with_unresolved_antlers()
    {
        Site::setSites([
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
                'lang' => '{{ config:app:locale }}',
                'attributes' => [
                    'theme' => '{{ config:statamic:some_addon:theme }}',
                ],
            ],
        ])->save();

        $expected = [
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
                'lang' => '{{ config:app:locale }}',
                'attributes' => [
                    'theme' => '{{ config:statamic:some_addon:theme }}',
                ],
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    #[Test]
    public function it_saves_multiple_sites_back_to_yaml_with_unresolved_antlers()
    {
        Site::setSites([
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
                'lang' => '{{ config:app:locale }}',
                'attributes' => [
                    'theme' => '{{ config:statamic:some_addon:theme }}',
                ],
            ],
            'arabic' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
                'lang' => '{{ config:app:locale }}',
                'attributes' => [
                    'theme' => '{{ config:statamic:some_addon:theme }}',
                ],
            ],
        ])->save();

        $expected = [
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
                'lang' => '{{ config:app:locale }}',
                'attributes' => [
                    'theme' => '{{ config:statamic:some_addon:theme }}',
                ],
            ],
            'arabic' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
                'lang' => '{{ config:app:locale }}',
                'attributes' => [
                    'theme' => '{{ config:statamic:some_addon:theme }}',
                ],
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    #[Test]
    public function it_saves_site_through_cp_endpoint()
    {
        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'name' => 'English',
                'handle' => 'default',
                'url' => '/',
                'locale' => 'en_US',
            ])
            ->assertSuccessful();

        $expected = [
            'default' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    #[Test]
    public function it_saves_multiple_sites_through_cp_endpoint()
    {
        // Multisite requires this config
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'sites' => [
                    [
                        'id' => 'abcde', // grid fieldtypes submit id, that should get stripped out
                        'name' => 'English',
                        'handle' => 'default',
                        'url' => '/',
                        'locale' => 'en_US',
                        'lang' => 'slang', // testing custom lang string, because it auto-sets itself off locale
                    ],
                    [
                        'id' => 'fghijk', // grid fieldtypes submit id, that should get stripped out
                        'name' => 'Arabic (Egypt)',
                        'handle' => 'arabic',
                        'url' => '/ar/',
                        'locale' => 'ar_EG',
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
                'url' => '/',
                'locale' => 'en_US',
                'lang' => 'slang',
            ],
            'arabic' => [
                'name' => 'Arabic (Egypt)',
                'url' => '/ar/',
                'locale' => 'ar_EG',
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    #[Test]
    public function it_validates_required_fields_for_site_through_cp_endpoint()
    {
        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [])
            ->assertStatus(422)
            ->assertJsonCount(4, 'errors')
            ->assertJson(['errors' => [
                'name' => ['This field is required.'],
                'handle' => ['This field is required.'],
                'url' => ['This field is required.'],
                'locale' => ['This field is required.'],
            ]]);
    }

    #[Test]
    public function it_validates_required_fields_for_multiple_sites_through_cp_endpoint()
    {
        // Multisite requires this config
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'sites' => [
                    [
                        'handle' => 'english', // this is a required field, so there should be only 3 failures here
                    ],
                    [
                        'lang' => 'en', // this is an optional field, so there should be 4 failures here
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonCount(7, 'errors')
            ->assertJson(['errors' => [
                'sites.0.name' => ['This field is required.'],
                'sites.0.url' => ['This field is required.'],
                'sites.0.locale' => ['This field is required.'],
                'sites.1.name' => ['This field is required.'],
                'sites.1.handle' => ['This field is required.'],
                'sites.1.url' => ['This field is required.'],
                'sites.1.locale' => ['This field is required.'],
            ]]);
    }

    public static function submitsNoSites()
    {
        return [
            'with no sites array' => [[]],
            'sites array with no elements' => [['sites' => []]],
            'sites null' => [['sites' => null]],
        ];
    }

    #[Test]
    #[DataProvider('submitsNoSites')]
    public function it_validates_at_least_one_site_is_required_for_multiple_sites_through_cp_endpoint($data)
    {
        // Multisite requires this config
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), $data)
            ->assertStatus(422)
            ->assertJsonCount(1, 'errors')
            ->assertJson(['errors' => [
                'sites' => ['This field is required.'],
            ]]);
    }

    #[Test]
    public function it_dispatches_site_saved_events()
    {
        Event::fake();

        Site::save();

        Event::assertDispatched(SiteSaved::class, 2);

        Event::assertDispatched(function (SiteSaved $event) {
            return $event->site->handle() === 'english';
        });

        Event::assertDispatched(function (SiteSaved $event) {
            return $event->site->handle() === 'french';
        });
    }

    #[Test]
    public function it_dispatches_site_created_events()
    {
        Event::fake();

        Site::setSites(
            collect(Site::config())
                ->put('german', ['name' => 'German', 'url' => '/de/'])
                ->put('polish', ['name' => 'Polish', 'url' => '/pl/'])
                ->all()
        )->save();

        Event::assertDispatched(SiteCreated::class, 2);

        Event::assertDispatched(function (SiteCreated $event) {
            return $event->site->handle() === 'german';
        });

        Event::assertDispatched(function (SiteCreated $event) {
            return $event->site->handle() === 'polish';
        });

        // We're saving a total of 4 sites to yaml after the above changes, so we should see 4 `SiteSaved` events as well
        Event::assertDispatched(SiteSaved::class, 4);
    }

    #[Test]
    public function it_dispatches_site_deleted_events()
    {
        Event::fake();

        Site::setSites(
            collect(Site::config())
                ->put('german', ['name' => 'German', 'url' => '/de/'])
                ->forget('english')
                ->forget('french')
                ->all()
        )->save();

        Event::assertDispatched(SiteDeleted::class, 2);

        Event::assertDispatched(function (SiteDeleted $event) {
            return $event->site->handle() === 'english';
        });

        Event::assertDispatched(function (SiteDeleted $event) {
            return $event->site->handle() === 'french';
        });

        // We're saving a total of 1 site to yaml after the above changes, so we should see 1 `SiteSaved` event as well
        Event::assertDispatched(SiteSaved::class, 1);
    }
}
