<?php

namespace Statamic\View;

use Illuminate\Http\Request;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Facades;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Sites\Site;
use Statamic\Support\Arr;

class Cascade
{
    protected $request;
    protected $site;
    protected $data;
    protected $content;
    protected $sections;
    protected $hydratedCallbacks = [];

    public function __construct(Request $request, Site $site, array $data = [])
    {
        $this->request = $request;
        $this->site = $site;
        $this->data($data);
        $this->sections = collect();
    }

    public function instance()
    {
        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function withRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    public function withSite($site)
    {
        $this->site = $site;

        return $this;
    }

    public function withContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function content()
    {
        return $this->content;
    }

    public function get($key)
    {
        return Arr::get($this->data, $key);
    }

    public function set($key, $value)
    {
        Arr::set($this->data, $key, $value);
    }

    public function data($data)
    {
        $this->data = $data;
    }

    public function hydrated($callback)
    {
        $this->hydratedCallbacks[] = $callback;

        return $this;
    }

    public function hydrate()
    {
        $this->data([]);
        $this->sections = collect();

        return $this
            ->hydrateVariables()
            ->hydrateSegments()
            ->hydrateGlobals()
            ->hydrateContent()
            ->hydrateViewModel()
            ->runHydratedCallbacks();
    }

    private function runHydratedCallbacks()
    {
        foreach ($this->hydratedCallbacks as $callback) {
            $callback($this);
        }

        return $this;
    }

    protected function hydrateVariables()
    {
        foreach ($this->contextualVariables() as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    protected function hydrateSegments()
    {
        $path = $this->site->relativePath($this->request->url());

        if ($path === '/') {
            return $this;
        }

        foreach (explode('/', $path) as $segment => $value) {
            $this->set("segment_{$segment}", $value);
        }

        $this->set('last_segment', $value);

        return $this;
    }

    protected function hydrateGlobals()
    {
        foreach (GlobalSet::all() as $global) {
            if ($global = $global->in($this->site->handle())) {
                $this->set($global->handle(), $global);
            }
        }

        if ($mainGlobal = $this->get('global')) {
            foreach ($mainGlobal->toDeferredAugmentedArray() as $key => $value) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    protected function hydrateContent()
    {
        if (! $this->content) {
            return $this;
        }

        if ($this->content instanceof \Closure) {
            $this->content = call_user_func($this->content);
        }

        $variables = $this->content instanceof Augmentable
            ? $this->content->toDeferredAugmentedArray()
            : $this->content->toArray();

        foreach ($variables as $key => $value) {
            $this->set($key, $value);
        }

        $this->set('page', $this->content);

        return $this;
    }

    private function contextualVariables()
    {
        return [
            // Constants
            'environment' => app()->environment(),
            'xml_header' => '<?xml version="1.0" encoding="utf-8" ?>', // @TODO remove and document new best practice
            'csrf_token' => csrf_token(),
            'csrf_field' => csrf_field(),
            'config' => static::config(),
            'response_code' => 200,

            // Auth
            'logged_in' => $loggedIn = auth(config('statamic.users.guards.web', 'web'))->check(),
            'logged_out' => ! $loggedIn,
            'current_user' => User::current(),

            // Date
            'current_date' => $now = now(),
            'now' => $now,
            'today' => $now,

            // Request
            'current_url' => $this->request->url(),
            'current_full_url' => $this->request->fullUrl(),
            'current_uri' => URL::tidy($this->request->path()),
            'get_post' => Arr::sanitize($this->request->all()),
            'get' => Arr::sanitize($this->request->query->all()),
            'post' => $this->request->isMethod('post') ? Arr::sanitize($this->request->request->all()) : [],
            'old' => Arr::sanitize(old(null, [])),

            'site' => $this->site,
            'sites' => Facades\Site::all()->values(),
            'homepage' => $this->site->url(),
            'is_homepage' => $this->site->absoluteUrl() == $this->request->url(),
            'cp_url' => cp_route('index'),
        ];
    }

    protected function hydrateViewModel()
    {
        if ($class = optional($this->get('view_model'))->value()) {
            $viewModel = new $class($this);
            $this->data = array_merge($this->data, $viewModel->data());
        }

        return $this;
    }

    public function getViewData($view)
    {
        $all = $this->get('views') ?? [];

        return collect($all)
            ->reverse()
            ->reduce(function ($carry, $data) {
                return $carry->merge($data);
            }, collect())
            ->merge($all[$view])
            ->all();
    }

    public function sections()
    {
        return $this->sections;
    }

    public function clearSections()
    {
        $this->sections = collect();

        return $this;
    }

    public static function config(): array
    {
        $defaults = [
            'app.name',
            'app.env',
            'app.debug',
            'app.url',
            'app.asset_url',
            'app.locale',
            'app.fallback_locale',
            'app.timezone',
            'auth.defaults',
            'auth.guards',
            'auth.passwords',
            'broadcasting.default',
            'cache.default',
            'filesystems.default',
            'mail.default',
            'mail.from',
            'queue.default',
            'session.lifetime',
            'session.expire_on_close',
            'session.driver',
            'statamic.assets.image_manipulation',
            'statamic.assets.auto_crop',
            'statamic.assets.thumbnails',
            'statamic.assets.video_thumbnails',
            'statamic.assets.google_docs_viewer',
            'statamic.assets.cache_meta',
            'statamic.assets.focal_point_editor',
            'statamic.assets.lowercase',
            'statamic.assets.svg_sanitization_on_upload',
            'statamic.assets.ffmpeg',
            'statamic.assets.set_preview_images',
            'statamic.autosave',
            'statamic.cp',
            'statamic.editions',
            'statamic.forms.email_view_folder',
            'statamic.forms.send_email_job',
            'statamic.forms.exporters',
            'statamic.git.enabled',
            'statamic.git.automatic',
            'statamic.git.queue_connection',
            'statamic.git.dispatch_delay',
            'statamic.git.use_authenticated',
            'statamic.git.user',
            'statamic.git.binary',
            'statamic.git.commands',
            'statamic.git.push',
            'statamic.git.ignored_events',
            'statamic.git.locale',
            'statamic.graphql',
            'statamic.live_preview',
            'statamic.markdown',
            'statamic.oauth',
            'statamic.protect.default',
            'statamic.revisions',
            'statamic.routes',
            'statamic.search.default',
            'statamic.search.indexes',
            'statamic.search.defaults',
            'statamic.search.queue',
            'statamic.search.queue_connection',
            'statamic.search.chunk_size',
            'statamic.stache.watcher',
            'statamic.stache.cache_store',
            'statamic.stache.indexes',
            'statamic.stache.lock',
            'statamic.stache.warming',
            'statamic.static_caching.strategy',
            'statamic.static_caching.strategies',
            'statamic.static_caching.exclude',
            'statamic.static_caching.invalidation',
            'statamic.static_caching.ignore_query_strings',
            'statamic.static_caching.allowed_query_strings',
            'statamic.static_caching.disallowed_query_strings',
            'statamic.static_caching.nocache',
            'statamic.static_caching.nocache_db_connection',
            'statamic.static_caching.replacers',
            'statamic.static_caching.warm_queue',
            'statamic.static_caching.warm_queue_connection',
            'statamic.static_caching.warm_insecure',
            'statamic.static_caching.background_recache',
            'statamic.static_caching.recache_token_parameter',
            'statamic.static_caching.share_errors',
            'statamic.system.multisite',
            'statamic.system.send_powered_by_header',
            'statamic.system.date_format',
            'statamic.system.display_timezone',
            'statamic.system.localize_dates_in_modifiers',
            'statamic.system.charset',
            'statamic.system.track_last_update',
            'statamic.system.cache_tags_enabled',
            'statamic.system.php_memory_limit',
            'statamic.system.php_max_execution_time',
            'statamic.system.ajax_timeout',
            'statamic.system.pcre_backtrack_limit',
            'statamic.system.debugbar',
            'statamic.system.ascii_replace_extra_symbols',
            'statamic.system.update_references',
            'statamic.system.always_augment_to_query',
            'statamic.system.row_id_handle',
            'statamic.system.fake_sql_queries',
            'statamic.system.layout',
            'statamic.templates',
            'statamic.users.repository',
            'statamic.users.avatars',
            'statamic.users.new_user_roles',
            'statamic.users.new_user_groups',
            'statamic.users.wizard_invitation',
            'statamic.users.passwords',
            'statamic.users.database',
            'statamic.users.tables',
            'statamic.users.guards',
            'statamic.users.impersonate',
            'statamic.users.elevated_session_duration',
            'statamic.users.two_factor_enforced_roles',
            'statamic.users.sort_field',
            'statamic.users.sort_direction',
            'statamic.webauthn',
        ];

        $allowed = collect((array) config('statamic.system.view_config_allowlist', $defaults))
            ->flatMap(fn ($key) => $key === '@default' ? $defaults : [$key])
            ->unique()->values()->all();

        return array_reduce($allowed, function ($config, $key) {
            $value = config($key);

            if (! is_null($value)) {
                Arr::set($config, $key, $value);
            }

            return $config;
        }, []);
    }
}
