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

    private function sitesField(array $section): array
    {
        return collect($section['fields'])->first(
            fn ($field) => str_ends_with($field['handle'], '_sites')
        );
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
        $this->assertTrue(Site::default()->isDefault());

        $this->assertSame('french', Site::get('french')->handle());
        $this->assertSame('French', Site::get('french')->name());
        $this->assertSame('/fr', Site::get('french')->url());
        $this->assertSame('fr_FR', Site::get('french')->locale());
        $this->assertSame('fr', Site::get('french')->lang());
        $this->assertFalse(Site::get('french')->isDefault());
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
        $this->assertSame(config('app.locale'), Site::default()->locale());
        $this->assertSame(config('app.locale'), Site::default()->lang());
        $this->assertTrue(Site::default()->isDefault());
    }

    #[Test]
    public function it_gets_default_site_with_empty_yaml()
    {
        File::put($this->yamlPath, '');

        // Ensure new sites instance in container, so that it attempts to read the empty yaml file
        Site::swap(new Sites);

        $this->assertCount(1, Site::all());
        $this->assertSame('default', Site::default()->handle());
        $this->assertSame('default', Site::current()->handle());
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
        Config::set('statamic.system.view_config_allowlist', ['@default', 'app.faker_locale', 'statamic.some_addon.theme']);

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
                'group_other_name' => null,
                'group_other_sites' => [
                    [
                        'id' => 'abcde', // grid fieldtypes submit id, that should get stripped out
                        'name' => 'English',
                        'handle' => 'default',
                        'url' => '/',
                        'locale' => 'en_US',
                        'lang' => 'slang', // testing custom lang string, because it auto-sets itself off locale
                    ],
                ],
                'group_middle-east_name' => 'Middle East',
                'group_middle-east_sites' => [
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
                'group' => 'Middle East',
                'group_handle' => 'middle-east',
                'attributes' => [
                    'theme' => 'standard',
                ],
            ],
        ];

        $this->assertSame($expected, YAML::file($this->yamlPath)->parse());
    }

    #[Test]
    public function it_saves_groups_in_submitted_order()
    {
        Config::set('statamic.system.multisite', true);

        Site::setSites([
            'en' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
                'group' => 'London',
            ],
            'paris' => [
                'name' => 'French',
                'url' => '/paris/',
                'locale' => 'fr_FR',
                'group' => 'Paris',
            ],
        ]);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'group_paris_name' => 'Paris',
                'group_paris_sites' => [
                    [
                        'name' => 'French',
                        'handle' => 'paris',
                        'url' => '/paris/',
                        'locale' => 'fr_FR',
                    ],
                ],
                'group_london_name' => 'London',
                'group_london_sites' => [
                    [
                        'name' => 'English',
                        'handle' => 'en',
                        'url' => '/',
                        'locale' => 'en_US',
                    ],
                ],
            ])
            ->assertSuccessful();

        $this->assertSame(['paris', 'en'], array_keys($saved = YAML::file($this->yamlPath)->parse()));
        $this->assertSame('Paris', $saved['paris']['group']);
        $this->assertSame('paris', $saved['paris']['group_handle']);
        $this->assertSame('London', $saved['en']['group']);
        $this->assertSame('london', $saved['en']['group_handle']);
    }

    #[Test]
    public function it_builds_grouped_blueprint_values()
    {
        Config::set('statamic.system.multisite', true);

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
                'group' => 'Middle East',
            ],
        ]);

        $values = Site::blueprintValues();

        $this->assertSame([
            'group_middle-east_name',
            'group_middle-east_sites',
            'group_other_sites',
        ], array_keys($values));
        $this->assertCount(1, $values['group_other_sites']);
        $this->assertSame('default', $values['group_other_sites'][0]['handle']);
        $this->assertSame('Middle East', $values['group_middle-east_name']);
        $this->assertCount(1, $values['group_middle-east_sites']);
        $this->assertSame('arabic', $values['group_middle-east_sites'][0]['handle']);
        $this->assertArrayNotHasKey('group', $values['group_middle-east_sites'][0]);
        $this->assertArrayNotHasKey('group_handle', $values['group_middle-east_sites'][0]);
    }

    #[Test]
    public function preprocessed_values_include_group_names()
    {
        Config::set('statamic.system.multisite', true);

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
                'group' => 'Middle East',
            ],
        ]);

        $values = Site::blueprint()
            ->fields()
            ->addValues(Site::blueprintValues())
            ->preProcess()
            ->values()
            ->all();

        $this->assertSame('Middle East', $values['group_middle-east_name']);
        $this->assertSame('arabic', $values['group_middle-east_sites'][0]['handle']);
        $this->assertSame('default', $values['group_other_sites'][0]['handle']);
    }

    #[Test]
    public function it_saves_a_newly_added_group_through_cp_endpoint()
    {
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'group_new-group_name' => 'Paris',
                'group_new-group_sites' => [
                    [
                        'name' => 'French',
                        'handle' => 'fr',
                        'url' => '/fr/',
                        'locale' => 'fr_FR',
                    ],
                ],
                'group_other_sites' => [
                    [
                        'name' => 'English',
                        'handle' => 'default',
                        'url' => '/',
                        'locale' => 'en_US',
                    ],
                ],
            ])
            ->assertSuccessful();

        $saved = YAML::file($this->yamlPath)->parse();

        $this->assertSame('Paris', $saved['fr']['group']);
        $this->assertSame('new-group', $saved['fr']['group_handle']);
        $this->assertArrayNotHasKey('group', $saved['default']);
        $this->assertArrayNotHasKey('group_handle', $saved['default']);
    }

    #[Test]
    public function grouped_blueprint_sections_are_reorderable()
    {
        Config::set('statamic.system.multisite', true);

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
                'group' => 'Middle East',
            ],
        ]);

        $sections = Site::blueprint()->contents()['tabs']['main']['sections'];

        $this->assertCount(2, $sections);
        $this->assertTrue($sections[0]['reorderable']);
        $this->assertArrayNotHasKey('reorderable', $sections[1]);
        $this->assertSame('group_middle-east_name', $sections[0]['editable_title_handle']);
        $this->assertArrayNotHasKey('editable_title_handle', $sections[1]);
        $this->assertSame('Other', $sections[1]['display']);
        $this->assertSame('group_middle-east_name', $sections[0]['fields'][0]['handle']);
        $this->assertSame('hidden', $sections[0]['fields'][0]['field']['visibility']);
        $this->assertSame('group_other_sites', $this->sitesField($sections[1])['handle']);
        $this->assertTrue($this->sitesField($sections[0])['field']['headers_in_section']);
        $this->assertTrue($this->sitesField($sections[1])['field']['headers_in_section']);
        $this->assertSame(
            'group_middle-east_name',
            collect($this->sitesField($sections[0])['field']['fields'])->firstWhere('handle', 'handle')['field']['prefix_from']
        );
        $this->assertArrayNotHasKey(
            'prefix_from',
            collect($this->sitesField($sections[1])['field']['fields'])->firstWhere('handle', 'handle')['field']
        );
    }

    #[Test]
    public function the_other_group_is_always_last_even_when_empty()
    {
        Config::set('statamic.system.multisite', true);

        Site::setSites([
            'en' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
                'group' => 'London',
            ],
        ]);

        $values = Site::blueprintValues();
        $sections = Site::blueprint()->contents()['tabs']['main']['sections'];

        $this->assertSame([
            'group_london_name',
            'group_london_sites',
            'group_other_sites',
        ], array_keys($values));
        $this->assertSame([], $values['group_other_sites']);
        $this->assertSame('group_london_name', $sections[0]['editable_title_handle']);
        $this->assertSame('group_other_sites', $this->sitesField($sections[1])['handle']);
        $this->assertArrayNotHasKey('reorderable', $sections[1]);
        $this->assertArrayNotHasKey('editable_title_handle', $sections[1]);
    }

    #[Test]
    public function it_includes_submitted_groups_in_the_blueprint()
    {
        Config::set('statamic.system.multisite', true);

        $sections = Site::blueprint([
            'group_other_sites' => [
                ['name' => 'English', 'handle' => 'default', 'url' => '/', 'locale' => 'en_US'],
            ],
            'group_paris_name' => 'Paris',
            'group_paris_sites' => [
                ['name' => 'French', 'handle' => 'paris', 'url' => '/paris/', 'locale' => 'fr_FR'],
            ],
        ])->contents()['tabs']['main']['sections'];

        $this->assertSame([
            'group_paris_name',
            null,
        ], collect($sections)->pluck('editable_title_handle')->all());
        $this->assertSame('group_paris_sites', $this->sitesField($sections[0])['handle']);
        $this->assertSame('group_other_sites', $this->sitesField($sections[1])['handle']);
        $this->assertSame('Other', $sections[1]['display']);
        $this->assertArrayNotHasKey('reorderable', $sections[1]);
    }

    #[Test]
    public function it_builds_config_from_grouped_blueprint_values()
    {
        Config::set('statamic.system.multisite', true);

        $config = Site::configFromBlueprintValues([
            'group_london_name' => 'London',
            'group_london_sites' => [
                ['name' => 'English', 'handle' => 'en', 'url' => '/', 'locale' => 'en_US'],
                ['name' => 'French', 'handle' => 'fr', 'url' => '/fr/', 'locale' => 'fr_FR'],
            ],
            'group_paris_name' => 'Paris',
            'group_paris_sites' => [
                ['name' => 'French', 'handle' => 'paris', 'url' => '/paris/', 'locale' => 'fr_FR'],
            ],
        ]);

        $this->assertSame('London', $config['en']['group']);
        $this->assertSame('london', $config['en']['group_handle']);
        $this->assertSame('London', $config['fr']['group']);
        $this->assertSame('london', $config['fr']['group_handle']);
        $this->assertSame('Paris', $config['paris']['group']);
        $this->assertSame('paris', $config['paris']['group_handle']);
        $this->assertArrayNotHasKey('group', $config['default'] ?? []);
        $this->assertSame(['en', 'fr', 'paris'], array_keys($config));
    }

    #[Test]
    public function it_preserves_group_order_from_blueprint_values()
    {
        Config::set('statamic.system.multisite', true);

        $config = Site::configFromBlueprintValues([
            'group_paris_name' => 'Paris',
            'group_paris_sites' => [
                ['name' => 'French', 'handle' => 'paris', 'url' => '/paris/', 'locale' => 'fr_FR'],
            ],
            'group_london_name' => 'London',
            'group_london_sites' => [
                ['name' => 'English', 'handle' => 'en', 'url' => '/', 'locale' => 'en_US'],
                ['name' => 'French', 'handle' => 'fr', 'url' => '/fr/', 'locale' => 'fr_FR'],
            ],
        ]);

        $this->assertSame(['paris', 'en', 'fr'], array_keys($config));
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
    public function it_validates_handles_are_unique_across_groups()
    {
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'group_london_name' => 'London',
                'group_london_sites' => [
                    [
                        'name' => 'English',
                        'handle' => 'en',
                        'url' => '/',
                        'locale' => 'en_US',
                    ],
                ],
                'group_paris_name' => 'Paris',
                'group_paris_sites' => [
                    [
                        'name' => 'English',
                        'handle' => 'en',
                        'url' => '/paris/',
                        'locale' => 'en_GB',
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'group_london_sites.0.handle',
                'group_paris_sites.0.handle',
            ]);
    }

    #[Test]
    public function it_validates_handles_are_unique_within_a_group()
    {
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'group_other_sites' => [
                    [
                        'name' => 'English',
                        'handle' => 'en',
                        'url' => '/',
                        'locale' => 'en_US',
                    ],
                    [
                        'name' => 'English UK',
                        'handle' => 'en',
                        'url' => '/uk/',
                        'locale' => 'en_GB',
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'group_other_sites.0.handle',
                'group_other_sites.1.handle',
            ]);
    }

    #[Test]
    public function it_validates_required_fields_for_multiple_sites_through_cp_endpoint()
    {
        // Multisite requires this config
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'group_other_sites' => [
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
                'group_other_sites.0.name' => ['This field is required.'],
                'group_other_sites.0.url' => ['This field is required.'],
                'group_other_sites.0.locale' => ['This field is required.'],
                'group_other_sites.1.name' => ['This field is required.'],
                'group_other_sites.1.handle' => ['This field is required.'],
                'group_other_sites.1.url' => ['This field is required.'],
                'group_other_sites.1.locale' => ['This field is required.'],
            ]]);
    }

    public static function submitsNoSites()
    {
        return [
            'with no sites array' => [[]],
            'sites array with no elements' => [['group_other_sites' => []]],
            'sites null' => [['group_other_sites' => null]],
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
            ->assertJsonValidationErrors(['group_other_sites']);
    }

    #[Test]
    public function it_validates_empty_named_groups_when_no_sites_are_submitted()
    {
        Config::set('statamic.system.multisite', true);

        $this
            ->actingAs(tap(User::make()->email('chew@bacca.com')->makeSuper())->save())
            ->patchJson(cp_route('sites.update'), [
                'group_london_name' => 'London',
                'group_london_sites' => [],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['group_london_sites'])
            ->assertJsonMissingValidationErrors(['group_other_sites']);
    }

    #[Test]
    public function groups_with_the_same_slug_stay_separate()
    {
        Config::set('statamic.system.multisite', true);

        Site::setSites([
            'en' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
                'group' => 'New York',
            ],
            'fr' => [
                'name' => 'French',
                'url' => '/fr/',
                'locale' => 'fr_FR',
                'group' => 'new york',
            ],
        ]);

        $values = Site::blueprintValues();

        $this->assertSame('New York', $values['group_new-york_name']);
        $this->assertSame('new york', $values['group_new-york-2_name']);
        $this->assertSame(['en'], collect($values['group_new-york_sites'])->pluck('handle')->all());
        $this->assertSame(['fr'], collect($values['group_new-york-2_sites'])->pluck('handle')->all());
    }

    #[Test]
    public function a_group_named_other_does_not_merge_into_the_ungrouped_section()
    {
        Config::set('statamic.system.multisite', true);

        Site::setSites([
            'en' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
                'group' => 'Other',
            ],
            'fr' => [
                'name' => 'French',
                'url' => '/fr/',
                'locale' => 'fr_FR',
            ],
        ]);

        $values = Site::blueprintValues();

        $this->assertSame('Other', $values['group_group_name']);
        $this->assertSame(['en'], collect($values['group_group_sites'])->pluck('handle')->all());
        $this->assertSame(['fr'], collect($values['group_other_sites'])->pluck('handle')->all());
    }

    #[Test]
    public function stored_group_handles_keep_groups_stable_when_names_slug_the_same()
    {
        Config::set('statamic.system.multisite', true);

        Site::setSites([
            'en' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
                'group' => 'Cafe',
                'group_handle' => 'cafe',
            ],
            'fr' => [
                'name' => 'French',
                'url' => '/fr/',
                'locale' => 'fr_FR',
                'group' => 'Café',
                'group_handle' => 'cafe-2',
            ],
        ]);

        $values = Site::blueprintValues();

        $this->assertSame('Cafe', $values['group_cafe_name']);
        $this->assertSame('Café', $values['group_cafe-2_name']);
        $this->assertSame(['en'], collect($values['group_cafe_sites'])->pluck('handle')->all());
        $this->assertSame(['fr'], collect($values['group_cafe-2_sites'])->pluck('handle')->all());
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
