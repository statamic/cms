<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Statamic\Console\RunsInPlease;
use Statamic\Search\ConfigUpdater;
use Symfony\Component\Process\PhpExecutableFinder;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

class InstallSearch extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:install:search
        { driver? : The search driver (local, algolia, or typesense) }
        { --index= : The search index to configure }
        { --build : Build the index after configuring it }';

    protected $description = 'Install and configure a search driver';

    private array $variables = [];
    private array $changes = [];
    private array $removals = [];
    private array $existing = [];
    private string $index;
    private string $prefix;

    public function handle(): int
    {
        $driver = $this->argument('driver') ?? select(
            label: __('statamic::search.driver_prompt'),
            options: ['local' => __('Local'), 'algolia' => 'Algolia', 'typesense' => 'Typesense'],
        );

        if (! in_array($driver, ['local', 'algolia', 'typesense'], true)) {
            $this->components->error(__('statamic::search.invalid_driver'));

            return self::FAILURE;
        }

        $this->index = $this->option('index') ?? text(
            label: __('Search index handle'),
            default: config('statamic.search.default', 'default'),
            required: true,
            validate: fn ($value) => preg_match('/^[a-zA-Z0-9_-]+$/', $value) ? null : __('statamic::search.invalid_handle'),
        );

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $this->index)) {
            $this->components->error(__('statamic::search.invalid_handle'));

            return self::FAILURE;
        }

        $this->variables = $this->changes = $this->removals = [];
        $this->existing = config("statamic.search.indexes.{$this->index}", []);
        $this->prefix = strtoupper($driver.($this->index === 'default' ? '' : '_'.$this->index));
        $this->set('driver', $driver);

        if ($this->existing === []) {
            $this->set('searchables', $this->index === 'cp' ? ['content', 'users', 'addons'] : 'content');
            $this->set('fields', ['title']);
        }

        if (($previous = $this->existing['driver'] ?? null) && $previous !== $driver) {
            foreach (['settings', 'credentials', 'client', 'nodes'] as $key) {
                $this->removals[] = ['indexes', $this->index, $key];
            }
        }

        $configPath = config_path('statamic/search.php');
        $source = File::get(File::exists($configPath) ? $configPath : __DIR__.'/../../../config/search.php');

        try {
            if ($driver === 'algolia') {
                $this->configureAlgolia();
            } elseif ($driver === 'typesense') {
                $this->configureTypesense();
            }

            $contents = app(ConfigUpdater::class)->render($source, $this->changes, $this->removals);
        } catch (\RuntimeException|ConnectionException $e) {
            $this->components->error($e instanceof ConnectionException ? __('statamic::search.connection_failed') : $e->getMessage());

            return self::FAILURE;
        }

        $this->installClient($driver);
        $this->writeEnvironment();
        File::ensureDirectoryExists(dirname($configPath));
        File::put($configPath, $contents);
        $this->callSilent('config:clear');
        $this->components->info(__('statamic::search.configured', ['index' => $this->index, 'driver' => $driver]));

        if ($this->option('build') || confirm(__('statamic::search.build_prompt'), false)) {
            // A fresh process loads both the new config and any newly installed SDK.
            $result = Process::path(base_path())
                ->env($this->variables)
                ->forever()
                ->run([
                    (new PhpExecutableFinder)->find(false) ?: 'php',
                    defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan',
                    'statamic:search:update',
                    $this->index,
                ], fn ($type, $output) => $this->output->write($output));

            return $result->successful() ? self::SUCCESS : self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function configureAlgolia(): void
    {
        $config = array_replace_recursive(config('statamic.search.drivers.algolia', []), $this->existing);
        $id = text(
            label: __('Algolia application ID'),
            default: Arr::get($config, 'credentials.id', ''),
            required: true,
            validate: fn ($value) => preg_match('/^[a-zA-Z0-9]+$/', $value) ? null : __('statamic::search.invalid_algolia_id'),
        );
        $key = $this->apiKey(__('Algolia admin API key'), Arr::get($config, 'credentials.secret', ''));

        if (! Http::timeout(10)->withHeaders([
            'X-Algolia-Application-Id' => $id,
            'X-Algolia-API-Key' => $key,
        ])->get("https://{$id}.algolia.net/1/indexes", ['length' => 1])->successful()) {
            throw new \RuntimeException('Could not authenticate with Algolia. Check the application ID and admin API key.');
        }

        $this->set('credentials.id', $this->environment('APP_ID', $id));
        $this->set('credentials.secret', $this->environment('SECRET', $key));
    }

    private function configureTypesense(): void
    {
        $config = array_replace_recursive(config('statamic.search.drivers.typesense', []), $this->existing);
        $node = Arr::get($config, 'client.nodes.0', Arr::get($config, 'nodes.0', []));
        $url = text(
            label: __('Typesense URL'),
            default: sprintf('%s://%s:%s%s', $node['protocol'] ?? 'http', $node['host'] ?? 'localhost', $node['port'] ?? '8108', $node['path'] ?? ''),
            required: true,
            validate: function ($value) {
                $url = parse_url($value);

                $valid = $url && in_array($url['scheme'] ?? null, ['http', 'https'], true)
                    && isset($url['host']) && ! isset($url['user']) && ! isset($url['pass'])
                    && ! isset($url['query']) && ! isset($url['fragment']);

                return $valid ? null : __('statamic::search.invalid_url');
            },
        );
        $url = rtrim($url, '/');
        $key = $this->apiKey(__('Typesense admin API key'), Arr::get($config, 'client.api_key', Arr::get($config, 'credentials.api_key', '')));
        $request = Http::timeout(10)->withHeaders(['X-TYPESENSE-API-KEY' => $key]);

        if (! $request->get($url.'/collections')->successful()) {
            throw new \RuntimeException('Could not authenticate with Typesense. Check the URL and admin API key.');
        }

        $urlParts = parse_url($url);
        // Legacy client credentials would otherwise override the new index credentials.
        $this->set('client', Arr::except($config['client'] ?? [], ['api_key', 'nodes', 'nearest_node', 'search_only_api_key']));
        $this->set('credentials.api_key', $this->environment('API_KEY', $key));
        $this->set('nodes', [[
            'host' => $this->environment('HOST', $urlParts['host']),
            'port' => $this->environment('PORT', (string) ($urlParts['port'] ?? ($urlParts['scheme'] === 'https' ? 443 : 80))),
            'protocol' => $this->environment('PROTOCOL', $urlParts['scheme']),
            'path' => $this->environment('PATH', $urlParts['path'] ?? ''),
        ]]);

        $searchKey = $this->apiKey(
            __('Typesense search-only API key (optional)'),
            Arr::get($config, 'credentials.search_only_api_key') ?? Arr::get($config, 'client.search_only_api_key', ''),
            false,
        );

        if ($searchKey !== '') {
            $this->set('credentials.search_only_api_key', $this->environment('SEARCH_ONLY_API_KEY', $searchKey));
        }

        if (confirm(__('Configure semantic search?'), false)) {
            $available = array_values(array_diff($this->existing['fields'] ?? ['title'], ['date']));

            if ($available === []) {
                throw new \RuntimeException('Configure at least one text field on the index before enabling semantic search.');
            }

            $fields = multiselect(
                label: __('statamic::search.embedding_fields_prompt'),
                options: $available,
                default: array_values(array_intersect(['title', 'content'], $available)),
                required: true,
            );
            $this->set('settings.embedding', [
                'from' => $fields,
                'model_config' => ['model_name' => 'ts/all-MiniLM-L12-v2'],
            ]);
        }

        if (confirm(__('Configure natural language search?'), false)) {
            $this->configureNaturalLanguage($request, $url, $config);
        }
    }

    private function configureNaturalLanguage(PendingRequest $request, string $url, array $config): void
    {
        $setup = select(
            label: __('Natural language model setup'),
            options: [
                'existing' => __('statamic::search.existing_nl_model'),
                'openai' => __('statamic::search.new_nl_model'),
            ],
        );
        $model = text(
            label: __('Typesense natural language model ID'),
            default: Arr::get($config, 'settings.natural_language.model', $setup === 'openai' ? 'statamic_'.$this->index : ''),
            required: true,
        );

        if ($setup === 'existing') {
            if (! $request->get($url.'/nl_search_models/'.rawurlencode($model))->successful()) {
                throw new \RuntimeException('Could not find that natural language model. Configure an LLM in Typesense first.');
            }

            $this->set('settings.natural_language', ['model' => $model]);

            return;
        }

        $name = text(__('OpenAI model name'), default: 'openai/gpt-4.1-mini', required: true);
        $key = $this->apiKey(__('OpenAI API key'), Arr::get($config, 'settings.natural_language.model_config.api_key', ''));
        $this->set('settings.natural_language', [
            'model' => $model,
            'model_config' => [
                'model_name' => str_starts_with($name, 'openai/') ? $name : 'openai/'.$name,
                'api_key' => $this->environment('NL_API_KEY', $key),
            ],
        ]);
    }

    private function apiKey(string $label, ?string $existing, bool $required = true): string
    {
        $existing ??= '';

        return password(
            label: $label,
            placeholder: $existing !== '' ? __('statamic::search.keep_key') : '',
            required: $required && $existing === '',
        ) ?: $existing;
    }

    private function set(string $path, mixed $value): void
    {
        $this->changes[] = [['indexes', $this->index, ...explode('.', $path)], $value];
    }

    private function environment(string $suffix, string $value): FuncCall
    {
        $key = $this->prefix.'_'.$suffix;
        $this->variables[$key] = $value;

        return new FuncCall(new Name('env'), [new Arg(new String_($key))]);
    }

    private function installClient(string $driver): void
    {
        $package = match ($driver) {
            'algolia' => 'algolia/algoliasearch-client-php',
            'typesense' => 'typesense/typesense-php',
            default => null,
        };

        if ($package && ! Composer::isInstalled($package)) {
            spin(
                callback: fn () => Composer::withoutQueue()->throwOnFailure()->require($package, $driver === 'typesense' ? '^6.0' : '^4.32'),
                message: __('statamic::search.installing_client', ['package' => $package]),
            );
        }
    }

    private function writeEnvironment(): void
    {
        if ($this->variables === []) {
            return;
        }

        $path = $this->laravel->environmentFilePath();
        $contents = File::exists($path) ? File::get($path) : '';

        foreach ($this->variables as $key => $value) {
            $escaped = str_replace(['\\', '"', '$', "\r", "\n"], ['\\\\', '\\"', '\\$', '\\r', '\\n'], $value);
            $line = $key.'="'.$escaped.'"';
            $pattern = '/^'.preg_quote($key, '/').'\h*=.*$/m';
            $contents = preg_match($pattern, $contents)
                ? preg_replace_callback($pattern, fn () => $line, $contents)
                : rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        File::put($path, $contents);
    }
}
