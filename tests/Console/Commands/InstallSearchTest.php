<?php

namespace Tests\Console\Commands;

use Dotenv\Dotenv;
use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\InstallSearch;
use Tests\TestCase;

class InstallSearchTest extends TestCase
{
    private string $directory;
    private string $configPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/statamic-install-search-'.Str::random(12);
        $this->app->useEnvironmentPath($this->directory);
        $this->app->useConfigPath($this->directory.'/config');
        $this->configPath = config_path('statamic/search.php');
        File::ensureDirectoryExists(dirname($this->configPath));
        File::put($this->configPath, File::get(__DIR__.'/../../../config/search.php'));
        File::put($this->directory.'/.env', "APP_NAME=Existing\n");
        Http::preventStrayRequests();
    }

    public function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    #[Test]
    public function local_search_needs_no_packages_or_credentials()
    {
        Composer::shouldReceive('isInstalled')->never();

        $this->artisan(InstallSearch::class, ['driver' => 'local', '--index' => 'default'])
            ->expectsConfirmation('Build the search index now?', false)
            ->assertSuccessful();

        $this->assertSame('local', (require $this->configPath)['indexes']['default']['driver']);
        $this->assertSame("APP_NAME=Existing\n", File::get($this->directory.'/.env'));
        Http::assertNothingSent();
    }

    #[Test]
    public function it_prompts_for_the_driver_and_index_when_not_supplied()
    {
        $this->artisan(InstallSearch::class)
            ->expectsChoice('Which search driver would you like to use?', 'local', ['local' => 'Local', 'algolia' => 'Algolia', 'typesense' => 'Typesense'])
            ->expectsQuestion('Search index handle', 'blog')
            ->expectsConfirmation('Build the search index now?', false)
            ->assertSuccessful();

        $this->assertSame('local', (require $this->configPath)['indexes']['blog']['driver']);
        $this->assertSame(['title'], (require $this->configPath)['indexes']['blog']['fields']);
    }

    #[Test]
    public function algolia_installs_its_sdk_and_keeps_secrets_out_of_php_config()
    {
        Composer::shouldReceive('isInstalled')->with('algolia/algoliasearch-client-php')->once()->andReturnFalse();
        Composer::shouldReceive('withoutQueue')->once()->andReturnSelf();
        Composer::shouldReceive('throwOnFailure')->once()->andReturnSelf();
        Composer::shouldReceive('require')->with('algolia/algoliasearch-client-php', '^4.32')->once();
        Http::fake(['*.algolia.net/*' => Http::response(['items' => []])]);

        $this->artisan(InstallSearch::class, ['driver' => 'algolia', '--index' => 'default'])
            ->expectsQuestion('Algolia application ID', 'APP123')
            ->expectsQuestion('Algolia admin API key', 'admin-secret')
            ->expectsConfirmation('Build the search index now?', false)
            ->assertSuccessful();

        $contents = File::get($this->configPath);
        $this->assertSame('algolia', (require $this->configPath)['indexes']['default']['driver']);
        $this->assertStringNotContainsString('admin-secret', $contents);
        $this->assertStringContainsString("env('ALGOLIA_SECRET')", $contents);
        $this->assertSame('admin-secret', $this->environment()['ALGOLIA_SECRET']);
        Http::assertSent(fn ($request) => $request->hasHeader('X-Algolia-API-Key', 'admin-secret'));
    }

    #[Test]
    public function typesense_accepts_a_cloud_url_and_leaves_ai_features_optional()
    {
        Composer::shouldReceive('isInstalled')->with('typesense/typesense-php')->once()->andReturnTrue();
        Http::fake(['https://cluster.typesense.net/*' => Http::response([])]);

        $this->artisan(InstallSearch::class, ['driver' => 'typesense', '--index' => 'default'])
            ->expectsQuestion('Typesense URL', 'https://cluster.typesense.net/proxy/')
            ->expectsQuestion('Typesense admin API key', 'admin-secret')
            ->expectsQuestion('Typesense search-only API key (optional)', '')
            ->expectsConfirmation('Configure semantic search?', false)
            ->expectsConfirmation('Configure natural language search?', false)
            ->expectsConfirmation('Build the search index now?', false)
            ->assertSuccessful();

        $env = $this->environment();
        $this->assertSame('cluster.typesense.net', $env['TYPESENSE_HOST']);
        $this->assertSame('443', $env['TYPESENSE_PORT']);
        $this->assertSame('/proxy', $env['TYPESENSE_PATH']);
        $this->assertSame('typesense', (require $this->configPath)['indexes']['default']['driver']);
        $this->assertArrayNotHasKey('settings', (require $this->configPath)['indexes']['default']);
        $this->assertStringNotContainsString('admin-secret', File::get($this->configPath));
        Http::assertSent(fn ($request) => $request->url() === 'https://cluster.typesense.net/proxy/collections');
    }

    #[Test]
    public function typesense_can_configure_semantic_search_and_an_existing_nl_model()
    {
        config(['statamic.search.indexes.default.fields' => ['title', 'content']]);
        File::put($this->configPath, str_replace("'fields' => ['title']", "'fields' => ['title', 'content']", File::get($this->configPath)));
        Composer::shouldReceive('isInstalled')->andReturnTrue();
        Http::fake(['https://cluster.typesense.net/*' => Http::response([])]);

        $this->artisan(InstallSearch::class, ['driver' => 'typesense', '--index' => 'default'])
            ->expectsQuestion('Typesense URL', 'https://cluster.typesense.net')
            ->expectsQuestion('Typesense admin API key', 'admin-secret')
            ->expectsQuestion('Typesense search-only API key (optional)', 'search-secret')
            ->expectsConfirmation('Configure semantic search?', 'yes')
            ->expectsChoice('Fields to use for semantic search', ['title'], ['title', 'content'])
            ->expectsConfirmation('Configure natural language search?', 'yes')
            ->expectsChoice('Natural language model setup', 'existing', [
                'existing' => 'Use an existing Typesense model',
                'openai' => 'Configure an OpenAI model',
            ])
            ->expectsQuestion('Typesense natural language model ID', 'articles')
            ->expectsConfirmation('Build the search index now?', false)
            ->assertSuccessful();

        $settings = (require $this->configPath)['indexes']['default']['settings'];
        $this->assertSame(['title'], $settings['embedding']['from']);
        $this->assertSame('ts/all-MiniLM-L12-v2', $settings['embedding']['model_config']['model_name']);
        $this->assertSame('articles', $settings['natural_language']['model']);
        $this->assertSame('search-secret', $this->environment()['TYPESENSE_SEARCH_ONLY_API_KEY']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/nl_search_models/articles'));
    }

    #[Test]
    public function it_can_configure_a_new_openai_model_without_writing_its_key_into_php()
    {
        Composer::shouldReceive('isInstalled')->andReturnTrue();
        Http::fake(['https://cluster.typesense.net/*' => Http::response([])]);

        $this->artisan(InstallSearch::class, ['driver' => 'typesense', '--index' => 'blog'])
            ->expectsQuestion('Typesense URL', 'https://cluster.typesense.net')
            ->expectsQuestion('Typesense admin API key', 'admin-secret')
            ->expectsQuestion('Typesense search-only API key (optional)', '')
            ->expectsConfirmation('Configure semantic search?', 'no')
            ->expectsConfirmation('Configure natural language search?', 'yes')
            ->expectsChoice('Natural language model setup', 'openai', [
                'existing' => 'Use an existing Typesense model',
                'openai' => 'Configure an OpenAI model',
            ])
            ->expectsQuestion('Typesense natural language model ID', 'blog_model')
            ->expectsQuestion('OpenAI model name', 'gpt-4.1-mini')
            ->expectsQuestion('OpenAI API key', 'llm-secret')
            ->expectsConfirmation('Build the search index now?', 'no')
            ->assertSuccessful();

        $settings = (require $this->configPath)['indexes']['blog']['settings'];
        $this->assertSame('blog_model', $settings['natural_language']['model']);
        $this->assertSame('openai/gpt-4.1-mini', $settings['natural_language']['model_config']['model_name']);
        $this->assertSame('llm-secret', $this->environment()['TYPESENSE_BLOG_NL_API_KEY']);
        $this->assertStringNotContainsString('llm-secret', File::get($this->configPath));
    }

    #[Test]
    public function updating_env_keys_preserves_unrelated_values_and_escapes_special_characters()
    {
        File::put($this->directory.'/.env', "APP_NAME=Existing\nALGOLIA_SECRET=old\n");
        $key = 'with"quotes\\slashes${VARIABLE}#hash';
        Composer::shouldReceive('isInstalled')->andReturnTrue();
        Http::fake(['*.algolia.net/*' => Http::response(['items' => []])]);

        $this->artisan(InstallSearch::class, ['driver' => 'algolia', '--index' => 'default'])
            ->expectsQuestion('Algolia application ID', 'APP123')
            ->expectsQuestion('Algolia admin API key', $key)
            ->expectsConfirmation('Build the search index now?', 'no')
            ->assertSuccessful();

        $this->assertSame($key, $this->environment()['ALGOLIA_SECRET']);
        $this->assertSame('Existing', $this->environment()['APP_NAME']);
        $this->assertSame(1, substr_count(File::get($this->directory.'/.env'), 'ALGOLIA_SECRET='));
    }

    #[Test]
    public function invalid_credentials_leave_existing_config_and_environment_untouched()
    {
        $original = File::get($this->configPath);
        Http::fake(['*' => Http::response(['message' => 'Forbidden'], 403)]);
        Composer::shouldReceive('isInstalled')->never();

        $this->artisan(InstallSearch::class, ['driver' => 'typesense', '--index' => 'default'])
            ->expectsQuestion('Typesense URL', 'https://cluster.typesense.net')
            ->expectsQuestion('Typesense admin API key', 'bad-key')
            ->assertFailed();

        $this->assertSame($original, File::get($this->configPath));
        $this->assertSame("APP_NAME=Existing\n", File::get($this->directory.'/.env'));
    }

    #[Test]
    public function it_preserves_custom_index_code_and_other_indexes_when_switching_drivers()
    {
        $source = <<<'PHP'
<?php

return [
    // Keep this comment and the transformer.
    'indexes' => [
        'blog' => [
            'driver' => 'algolia',
            'fields' => ['title', 'content'],
            'searchables' => 'collection:blog',
            'filter' => fn ($entry) => $entry->published(),
            'transformers' => ['title' => fn ($value) => strtoupper($value)],
            'settings' => ['searchableAttributes' => ['title']],
        ],
        'other' => ['driver' => 'local', 'fields' => ['title']],
    ],
];
PHP;
        File::put($this->configPath, $source);
        config(['statamic.search.indexes.blog' => (require $this->configPath)['indexes']['blog']]);

        $this->artisan(InstallSearch::class, ['driver' => 'local', '--index' => 'blog'])
            ->expectsConfirmation('Build the search index now?', false)
            ->assertSuccessful();

        $config = require $this->configPath;
        $this->assertSame('local', $config['indexes']['blog']['driver']);
        $this->assertSame(['title', 'content'], $config['indexes']['blog']['fields']);
        $this->assertSame('collection:blog', $config['indexes']['blog']['searchables']);
        $this->assertSame('TITLE', $config['indexes']['blog']['transformers']['title']('title'));
        $this->assertArrayNotHasKey('settings', $config['indexes']['blog']);
        $this->assertSame(['driver' => 'local', 'fields' => ['title']], $config['indexes']['other']);
        $this->assertStringContainsString('// Keep this comment and the transformer.', File::get($this->configPath));
    }

    #[Test]
    public function building_uses_a_fresh_process_and_passes_the_selected_index()
    {
        Process::fake();

        $this->artisan(InstallSearch::class, ['driver' => 'local', '--index' => 'blog', '--build' => true])
            ->assertSuccessful();

        Process::assertRan(fn ($process) => array_slice($process->command, -2) === ['statamic:search:update', 'blog']);
    }

    #[Test]
    public function it_publishes_the_search_config_when_it_is_missing()
    {
        File::delete($this->configPath);

        $this->artisan(InstallSearch::class, ['driver' => 'local', '--index' => 'blog'])
            ->expectsConfirmation('Build the search index now?', 'no')
            ->assertSuccessful();

        $this->assertSame('local', (require $this->configPath)['indexes']['blog']['driver']);
        $this->assertArrayHasKey('algolia', (require $this->configPath)['drivers']);
    }

    #[Test]
    public function legacy_typesense_client_credentials_do_not_override_the_new_connection()
    {
        config(['statamic.search.drivers.typesense.client' => [
            'api_key' => 'legacy-secret',
            'nodes' => [['host' => 'old.test', 'port' => '8108', 'protocol' => 'http']],
            'nearest_node' => ['host' => 'old.test', 'port' => '8108', 'protocol' => 'http'],
            'num_retries' => 3,
        ]]);
        Composer::shouldReceive('isInstalled')->andReturnTrue();
        Http::fake(['https://new.test/*' => Http::response([])]);

        $this->artisan(InstallSearch::class, ['driver' => 'typesense', '--index' => 'blog'])
            ->expectsQuestion('Typesense URL', 'https://new.test')
            ->expectsQuestion('Typesense admin API key', 'new-secret')
            ->expectsQuestion('Typesense search-only API key (optional)', '')
            ->expectsConfirmation('Configure semantic search?', 'no')
            ->expectsConfirmation('Configure natural language search?', 'no')
            ->expectsConfirmation('Build the search index now?', 'no')
            ->assertSuccessful();

        $index = (require $this->configPath)['indexes']['blog'];
        $this->assertSame(['num_retries' => 3], $index['client']);
        $this->assertSame('new.test', $this->environment()['TYPESENSE_BLOG_HOST']);
        $this->assertSame('new-secret', $this->environment()['TYPESENSE_BLOG_API_KEY']);
        $this->assertArrayNotHasKey('TYPESENSE_API_KEY', $this->environment());
    }

    #[Test]
    public function a_missing_natural_language_model_leaves_files_untouched()
    {
        $original = File::get($this->configPath);
        Http::fake([
            'https://cluster.typesense.net/collections' => Http::response([]),
            'https://cluster.typesense.net/nl_search_models/missing' => Http::response([], 404),
        ]);

        $this->artisan(InstallSearch::class, ['driver' => 'typesense', '--index' => 'default'])
            ->expectsQuestion('Typesense URL', 'https://cluster.typesense.net')
            ->expectsQuestion('Typesense admin API key', 'admin-secret')
            ->expectsQuestion('Typesense search-only API key (optional)', '')
            ->expectsConfirmation('Configure semantic search?', 'no')
            ->expectsConfirmation('Configure natural language search?', 'yes')
            ->expectsChoice('Natural language model setup', 'existing', [
                'existing' => 'Use an existing Typesense model',
                'openai' => 'Configure an OpenAI model',
            ])
            ->expectsQuestion('Typesense natural language model ID', 'missing')
            ->assertFailed();

        $this->assertSame($original, File::get($this->configPath));
        $this->assertSame("APP_NAME=Existing\n", File::get($this->directory.'/.env'));
    }

    #[Test]
    public function a_failed_build_returns_a_failure_without_discarding_the_saved_configuration()
    {
        Process::fake([Process::result(exitCode: 1)]);

        $this->artisan(InstallSearch::class, ['driver' => 'local', '--index' => 'blog', '--build' => true])
            ->assertFailed();

        $this->assertSame('local', (require $this->configPath)['indexes']['blog']['driver']);
    }

    #[Test]
    public function unsupported_dynamic_config_is_not_rewritten_or_evaluated_again()
    {
        $source = "<?php\nreturn array_merge([], ['indexes' => []]);\n";
        File::put($this->configPath, $source);

        $this->artisan(InstallSearch::class, ['driver' => 'local', '--index' => 'blog'])
            ->assertFailed();

        $this->assertSame($source, File::get($this->configPath));
    }

    #[Test]
    public function invalid_drivers_are_rejected_without_changes()
    {
        $original = File::get($this->configPath);

        $this->artisan(InstallSearch::class, ['driver' => 'unknown'])->assertFailed();

        $this->assertSame($original, File::get($this->configPath));
    }

    private function environment(): array
    {
        return Dotenv::parse(File::get($this->directory.'/.env'));
    }
}
