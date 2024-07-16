<?php

namespace Tests\UpdateScripts;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\UpdateScripts\MigrateSitesConfigToYaml;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class MigrateSitesConfigToYamlTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, RunsUpdateScripts;

    public function setUp(): void
    {
        parent::setUp();

        // Start with v4 system config, since most people will have that published
        File::makeDirectory(config_path('statamic'), 0755, true);
        File::copy(__DIR__.'/__fixtures__/v4/config/system.php', config_path('statamic/system.php'));

        // Delete default sites.yaml, since base test copies one in
        File::delete(resource_path('sites.yaml'));
    }

    public function tearDown(): void
    {
        // Put back the current system config for other tests
        File::delete(config_path('statamic/system.php'));

        parent::tearDown();
    }

    #[Test]
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(MigrateSitesConfigToYaml::class);
    }

    #[Test]
    public function it_can_migrate_vanilla_sites_config()
    {
        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sites
    |--------------------------------------------------------------------------
    |
    | Each site should have root URL that is either relative or absolute. Sites
    | are typically used for localization (eg. English/French) but may also
    | be used for related content (eg. different franchise locations).
    |
    */

    'sites' => [

        'default' => [
            'name' => config('app.name'),
            'locale' => 'en_US',
            'url' => '/',
        ],

    ],

];
CONFIG);

        $this->migrateSitesConfig();

        $this->assertMultisiteEnabledConfigIs(false);

        $this->assertSitesYamlHas([
            'default' => [
                'name' => '{{ config:app:name }}',
                'locale' => 'en_US',
                'url' => '/',
            ],
        ]);
    }

    #[Test]
    public function it_can_migrate_modified_single_site_config()
    {
        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [
    'sites' => [
        'english' => [
            'name' => 'English',
            'locale' => 'en_US',
            'url' => '/',
        ],
    ],
];
CONFIG);

        $this->migrateSitesConfig();

        $this->assertMultisiteEnabledConfigIs(false);

        $this->assertSitesYamlHas([
            'english' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
            ],
        ]);
    }

    #[Test]
    public function it_can_migrate_modified_multisite_config()
    {
        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [
    'sites' => [
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
    ],
];
CONFIG);

        $this->migrateSitesConfig();

        $this->assertMultisiteEnabledConfigIs(true);

        $this->assertSitesYamlHas([
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
        ]);
    }

    #[Test]
    public function it_can_migrate_dynamic_config_function_calls()
    {
        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [
    'sites' => [
        'english' => [
            'name' => config('app.name'),
            'url' => config('app.url'),
            'locale' => config('app.faker_locale'),
        ],
        'french' => [
            'name' => config('app.french.name'),
            'url' => config('app.french.url'),
            'locale' => config('app.french.faker_locale'),
        ],
    ],
];
CONFIG);

        $this->migrateSitesConfig();

        $this->assertMultisiteEnabledConfigIs(true);

        $this->assertSitesYamlHas([
            'english' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => '{{ config:app:faker_locale }}',
            ],
            'french' => [
                'name' => '{{ config:app:french:name }}',
                'url' => '{{ config:app:french:url }}',
                'locale' => '{{ config:app:french:faker_locale }}',
            ],
        ]);
    }

    #[Test]
    public function it_can_migrate_dynamic_whitelisted_env_function_calls()
    {
        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [
    'sites' => [
        'default' => [
            'name' => env('APP_NAME'),
            'url' => env('APP_URL'),
            'locale' => 'en_US',
        ],
    ],
];
CONFIG);

        $this->migrateSitesConfig();

        $this->assertMultisiteEnabledConfigIs(false);

        $this->assertSitesYamlHas([
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '{{ config:app:url }}',
                'locale' => 'en_US',
            ],
        ]);
    }

    #[Test]
    public function it_warns_when_it_detects_other_non_whitelisted_env_calls()
    {
        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [
    'sites' => [
        'default' => [
            'name' => env('CUSTOM_NAME'),
            'url' => env('CUSTOM_URL'),
            'locale' => 'en_US',
        ],
    ],
];
CONFIG);

        $script = $this->migrateSitesConfig();

        $this->assertMultisiteEnabledConfigIs(false);

        $this->assertSitesYamlHas([
            'default' => [
                'name' => null,
                'url' => null,
                'locale' => 'en_US',
            ],
        ]);

        $this->assertCount(0, $script->console()->getErrors());
        $this->assertCount(1, $warnings = $script->console()->getWarnings());
        $this->assertStringContainsString('CUSTOM_NAME', $warnings->first());
        $this->assertStringContainsString('CUSTOM_URL', $warnings->first());
    }

    #[Test]
    public function it_can_append_multisite_config_to_bottom_if_the_nicer_str_replace_fails()
    {
        // For example, maybe they removed their comment blocks from their system.php config for some reason...
        File::put(config_path('statamic/system.php'), <<<'CONFIG'
<?php

return [

    'license_key' => env('STATAMIC_LICENSE_KEY'),

];
CONFIG);

        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [
    'sites' => [
        'english' => [
            'name' => 'English',
            'locale' => 'en_US',
            'url' => '/',
        ],
    ],
];
CONFIG);

        $this->migrateSitesConfig();

        $this->assertStringContainsString(<<<'CONFIG'
<?php

return [

    'license_key' => env('STATAMIC_LICENSE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Enable Multi-site
    |--------------------------------------------------------------------------
    |
    | Whether Statamic's multi-site functionality should be enabled. It is
    | assumed Statamic Pro is also enabled. To get started, you can run
    | the `php please multisite` command to update your content file
    | structure, after which you can manage your sites in the CP.
    |
    | https://statamic.dev/multi-site
    |
    */

    'multisite' => false,

];
CONFIG, File::get(config_path('statamic/system.php')));
    }

    #[Test]
    public function it_removes_text_direction_since_this_no_longer_does_anything_in_sites_yaml()
    {
        File::put(config_path('statamic/sites.php'), <<<'CONFIG'
<?php

return [
    'sites' => [
        'english' => [
            'name' => 'English',
            'url' => '/',
            'locale' => 'en_US',
            'direction' => 'ltr',
        ],
        'arabic' => [
            'name' => 'Arabic',
            'url' => '/ar/',
            'locale' => 'ar_SA',
            'direction' => 'rtl',
        ],
    ],
];
CONFIG);

        $this->migrateSitesConfig();

        $this->assertMultisiteEnabledConfigIs(true);

        $this->assertSitesYamlHas([
            'english' => [
                'name' => 'English',
                'url' => '/',
                'locale' => 'en_US',
            ],
            'arabic' => [
                'name' => 'Arabic',
                'url' => '/ar/',
                'locale' => 'ar_SA',
            ],
        ]);
    }

    private function migrateSitesConfig()
    {
        $script = $this->runUpdateScript(MigrateSitesConfigToYaml::class);

        $this->assertFileDoesNotExist(config_path('statamic/sites.php'));
        $this->assertNull(config('statamic.sites.sites'));

        $this->assertFileExists(resource_path('sites.yaml'));

        return $script;
    }

    private function assertMultisiteEnabledConfigIs($boolean)
    {
        $boolean = $boolean === true ? 'true' : 'false';

        $this->assertStringContainsString(<<<"CONFIG"
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Key
    |--------------------------------------------------------------------------
    |
    | The license key for the corresponding domain from your Statamic account.
    | Without a key entered, your app will considered to be in Trial Mode.
    |
    | https://statamic.dev/licensing#trial-mode
    |
    */

    'license_key' => env('STATAMIC_LICENSE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Enable Multi-site
    |--------------------------------------------------------------------------
    |
    | Whether Statamic's multi-site functionality should be enabled. It is
    | assumed Statamic Pro is also enabled. To get started, you can run
    | the `php please multisite` command to update your content file
    | structure, after which you can manage your sites in the CP.
    |
    | https://statamic.dev/multi-site
    |
    */

    'multisite' => {$boolean},

    /*
    |--------------------------------------------------------------------------
    | Default Addons Paths
    |--------------------------------------------------------------------------
CONFIG, File::get(config_path('statamic/system.php')));
    }

    private function assertSitesYamlHas($sites)
    {
        $this->assertSame($sites, YAML::file(resource_path('sites.yaml'))->parse());
    }
}
