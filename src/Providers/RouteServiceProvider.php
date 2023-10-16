<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Mixins\Router;
use Statamic\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            \Statamic\Contracts\Routing\UrlBuilder::class,
            \Statamic\Routing\UrlBuilder::class
        );
    }

    public function boot()
    {
        Route::mixin(new Router);

        $this->bindCollections();
        $this->bindEntries();
        $this->bindTaxonomies();
        $this->bindTerms();
        $this->bindAssetContainers();
        $this->bindAssets();
        $this->bindGlobalSets();
        $this->bindSites();
        $this->bindRevisions();
        $this->bindForms();
    }

    protected function bindCollections()
    {
        Route::bind('collection', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['collection'] ?? 'handle';
            $collection = $field == 'handle' ? Collection::findByHandle($handle) : Collection::all()->firstWhere($field, $handle);

            throw_unless(
                $collection,
                new NotFoundHttpException("Collection [$handle] not found.")
            );

            return $collection;
        });
    }

    protected function bindEntries()
    {
        Route::bind('entry', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if ($this->isApiRoute($route) || ! $this->isCpRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['entry'] ?? 'id';
            $entry = $field == 'id' ? Entry::find($handle) : Entry::query()->where($field, $handle)->first();

            throw_if(
                ! ($entry) || ($route->parameter('collection') && ($entry->collection()->id() !== $route->parameter('collection')->id())),
                new NotFoundHttpException("Entry [$handle] not found.")
            );

            return $entry;
        });
    }

    protected function bindTaxonomies()
    {
        Route::bind('taxonomy', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['taxonomy'] ?? 'handle';
            $taxonomy = $field == 'handle' ? Taxonomy::findByHandle($handle) : Taxonomy::all()->firstWhere($field, $handle);

            throw_unless(
                $taxonomy,
                new NotFoundHttpException("Taxonomy [$handle] not found.")
            );

            return $taxonomy;
        });
    }

    protected function bindTerms()
    {
        Route::bind('term', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if ($this->isApiRoute($route) || ! $this->isCpRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['term'] ?? 'id';

            $handle = ($field == 'id' && $route->parameter('taxonomy') ? $route->parameter('taxonomy')->handle().'::' : '').$handle;
            $site = $route->parameter('site') ?? Site::default()->handle();

            $term = $field == 'id' ? Term::find($handle)->in($site) : Term::query()->where($field, $handle)->where('site', $site)->when($route->parameter('taxonomy'), fn ($query) => $query->where('taxonomy', $route->parameter('taxonomy')->handle()))->first();

            throw_unless(
                $term && ($route->parameter('taxonomy') && $term->taxonomy()->id() === $route->parameter('taxonomy')->id()),
                new NotFoundHttpException("Taxonomy term [$handle] not found.")
            );

            return $term;
        });
    }

    protected function bindAssetContainers()
    {
        Route::bind('asset_container', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['asset_container'] ?? 'handle';
            $container = $field == 'handle' ? AssetContainer::findByHandle($handle) : AssetContainer::all()->firstWhere($field, $handle);

            throw_unless(
                $container,
                new NotFoundHttpException("Asset container [$handle] not found.")
            );

            return $container;
        });
    }

    protected function bindAssets()
    {
        Route::bind('asset', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['asset'] ?? 'id';

            $handle = ($field == 'id' && $route->parameter('asset_container') ? $route->parameter('asset_container')->handle().'::' : '').$handle;

            $asset = $field == 'id' ? Asset::find($handle) : Asset::query()->where($field, $handle)->when($route->parameter('asset_container'), fn ($query) => $query->where('container', $route->parameter('container')->handle()))->first();

            throw_unless(
                $asset,
                new NotFoundHttpException("Asset [$handle] not found.")
            );

            return $asset;
        });
    }

    protected function bindGlobalSets()
    {
        Route::bind('global', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['global'] ?? 'handle';
            $global = $field == 'handle' ? GlobalSet::findByHandle($handle) : GlobalSet::all()->firstWhere($field, $handle);

            $site = Site::default()->handle();

            throw_unless(
                $globalSet = $global->in($site),
                new NotFoundHttpException("Global set [$handle] not found.")
            );

            return $globalSet;
        });
    }

    protected function bindSites()
    {
        Route::bind('site', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['site'] ?? 'handle';
            $site = $field == 'handle' ? Site::get($handle) : Site::all()->firstWhere($field, $handle);

            throw_unless(
                $site,
                new NotFoundHttpException("Site [$handle] not found.")
            );

            return $site;
        });
    }

    protected function bindRevisions()
    {
        Route::bind('revision', function ($reference, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)) {
                    return $reference;
                }
            }

            if ($route->hasParameter('entry')) {
                $content = $route->parameter('entry');
            } elseif ($route->hasParameter('term')) {
                $content = $route->parameter('term');
            } else {
                throw new NotFoundHttpException;
            }

            throw_unless(
                $revision = $content->revision($reference),
                new NotFoundHttpException("Revision [$reference] not found.")
            );

            return $revision;
        });
    }

    protected function bindForms()
    {
        Route::bind('form', function ($handle, $route = null) {
            if (! $this->isFrontendBindingEnabled()) {
                if (! $this->isCpOrApiRoute($route)
                    && ! Str::startsWith($route->uri(), config('statamic.routes.action').'/forms/')) {
                    return $handle;
                }
            }

            $field = $route->bindingFields()['form'] ?? 'handle';
            $form = $field == 'handle' ? Form::find($handle) : Form::all()->firstWhere($field, $handle);

            throw_unless(
                $form,
                new NotFoundHttpException("Form [$handle] not found.")
            );

            return $form;
        });
    }

    private function isFrontendBindingEnabled()
    {
        return config('statamic.routes.binding_enabled', false);
    }

    private function isApiRoute(\Illuminate\Routing\Route $route = null)
    {
        if (is_null($route)) {
            return false;
        }

        $api = Str::ensureRight(config('statamic.api.route'), '/');

        if ($api === '/') {
            return true;
        }

        return Str::startsWith($route->uri(), $api);
    }

    private function isCpRoute(\Illuminate\Routing\Route $route = null)
    {
        if (is_null($route)) {
            return false;
        }

        $cp = Str::ensureRight(config('statamic.cp.route'), '/');

        if ($cp === '/') {
            return true;
        }

        return Str::startsWith($route->uri(), $cp);
    }

    private function isCpOrApiRoute(\Illuminate\Routing\Route $route = null)
    {
        if (is_null($route)) {
            return false;
        }

        return $this->isCpRoute($route) || $this->isApiRoute($route);
    }
}
