<?php

namespace Tests\UpdateScripts;

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
        File::copy(__DIR__.'/__fixtures__/v4/config/system.php', config_path('statamic/system.php'));

        // Delete default sites.yaml, since base test copies one in
        File::delete(base_path('content/sites.yaml'));
    }

    public function tearDown(): void
    {
        // Put back the current system config for other tests
        File::copy(__DIR__.'/../../config/system.php', config_path('statamic/system.php'));

        parent::tearDown();
    }

    /** @test */
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(MigrateSitesConfigToYaml::class);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    private function migrateSitesConfig()
    {
        $this->runUpdateScript(MigrateSitesConfigToYaml::class);

        $this->assertFileDoesNotExist(config_path('statamic/sites.php'));
        $this->assertNull(config('statamic.sites.sites'));

        $this->assertFileExists(base_path('content/sites.yaml'));
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
        $this->assertSame($sites, YAML::file(base_path('content/sites.yaml'))->parse());
    }
}
