<?php

namespace Statamic\Providers;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Routes;
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
        Routes::mixin(new Router);

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
        Routes::bind('collection', function ($handle, $route = null) {
            if (! $this->needsCollectionBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('collection') ?? 'handle';

            $collection = $field == 'handle'
                ? Collection::findByHandle($handle)
                : Collection::all()->firstWhere($field, $handle);

            throw_unless(
                $collection,
                new NotFoundHttpException("Collection [$handle] not found.")
            );

            return $collection;
        });
    }

    private function needsCollectionBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        return $this->isCpOrApiRoute($route) || $this->isFrontendBindingEnabled();
    }

    protected function bindEntries()
    {
        Routes::bind('entry', function ($handle, $route = null) {
            if (! $this->needsEntryBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('entry') ?? 'id';

            $entry = $field == 'id'
                ? Entry::find($handle)
                : Entry::query()->where($field, $handle)->first();

            $collection = $route->parameter('collection');

            throw_if(
                ! $entry || ($collection && $entry->collection()->id() !== $collection->id()),
                new NotFoundHttpException("Entry [$handle] not found.")
            );

            return $entry;
        });
    }

    private function needsEntryBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpRoute($route)) {
            return true;
        }

        if ($this->isApiRoute($route)) {
            return false;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindTaxonomies()
    {
        Routes::bind('taxonomy', function ($handle, $route = null) {
            if (! $this->needsTaxonomyBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('taxonomy') ?? 'handle';

            $taxonomy = $field == 'handle'
                ? Taxonomy::findByHandle($handle)
                : Taxonomy::all()->firstWhere($field, $handle);

            throw_unless(
                $taxonomy,
                new NotFoundHttpException("Taxonomy [$handle] not found.")
            );

            return $taxonomy;
        });
    }

    private function needsTaxonomyBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpOrApiRoute($route)) {
            return true;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindTerms()
    {
        Routes::bind('term', function ($handle, $route = null) {
            if (! $this->needsTermBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('term') ?? 'id';

            $taxonomy = $route->parameter('taxonomy');

            if ($field == 'id' && $taxonomy) {
                $handle = $taxonomy->handle().'::'.$handle;
            }

            $site = $route->parameter('site') ?? Site::default()->handle();

            $term = $field == 'id'
                ? Term::find($handle)?->in($site)
                : Term::query()
                    ->where($field, $handle)
                    ->where('site', $site)
                    ->when($taxonomy, fn ($query) => $query->where('taxonomy', $taxonomy->handle()))
                    ->first();

            throw_unless(
                $term || ($term && $taxonomy && $term->taxonomy()->id() === $taxonomy->id()),
                new NotFoundHttpException("Taxonomy term [$handle] not found.")
            );

            return $term;
        });
    }

    private function needsTermBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpRoute($route)) {
            return true;
        }

        if ($this->isApiRoute($route)) {
            return false;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindAssetContainers()
    {
        Routes::bind('asset_container', function ($handle, $route = null) {
            if (! $this->needsAssetContainerBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('asset_container') ?? 'handle';

            $container = $field == 'handle'
                ? AssetContainer::findByHandle($handle)
                : AssetContainer::all()->firstWhere($field, $handle);

            throw_unless(
                $container,
                new NotFoundHttpException("Asset container [$handle] not found.")
            );

            return $container;
        });
    }

    private function needsAssetContainerBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpOrApiRoute($route)) {
            return true;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindAssets()
    {
        Routes::bind('asset', function ($handle, $route = null) {
            if (! $this->needsAssetBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('asset') ?? 'id';

            $container = $route->parameter('asset_container');

            if ($field == 'id') {
                $handle = $container->handle().'::'.$handle;
            }

            $asset = $field == 'id'
                ? Asset::find($handle)
                : $container->queryAssets()->where($field, $handle)->first();

            throw_unless(
                $asset,
                new NotFoundHttpException("Asset [$handle] not found.")
            );

            return $asset;
        });
    }

    private function needsAssetBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpOrApiRoute($route)) {
            return true;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindGlobalSets()
    {
        Routes::bind('global', function ($handle, $route = null) {
            if (! $this->needsGlobalsBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('global') ?? 'handle';

            $global = $field == 'handle'
                ? GlobalSet::findByHandle($handle)
                : GlobalSet::all()->first(fn ($set) => $set->$field($handle));

            if (! $site = ($this->isApiRoute($route) ? request()->input('site') : false)) {
                $site = Site::default()->handle();
            }

            throw_unless(
                $globalSet = $global?->in($site),
                new NotFoundHttpException("Global set [$handle] not found.")
            );

            return $globalSet;
        });
    }

    private function needsGlobalsBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpOrApiRoute($route)) {
            return true;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindSites()
    {
        Routes::bind('site', function ($handle, $route = null) {
            if (! $this->needsSiteBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('site') ?? 'handle';

            $site = $field == 'handle'
                ? Site::get($handle)
                : Site::all()->firstWhere($field, $handle);

            throw_unless(
                $site,
                new NotFoundHttpException("Site [$handle] not found.")
            );

            return $site;
        });
    }

    private function needsSiteBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpOrApiRoute($route)) {
            return true;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindRevisions()
    {
        Routes::bind('revision', function ($reference, $route = null) {
            if (! $this->needsRevisionBinding($route)) {
                return $reference;
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

    private function needsRevisionBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpRoute($route)) {
            return true;
        }

        if ($this->isApiRoute($route)) {
            return false;
        }

        return $this->isFrontendBindingEnabled();
    }

    protected function bindForms()
    {
        Routes::bind('form', function ($handle, $route = null) {
            if (! $this->needsFormBinding($route)) {
                return $handle;
            }

            $field = $route->bindingFieldFor('form') ?? 'handle';

            $form = $field == 'handle'
                ? Form::find($handle)
                : Form::all()->firstWhere($field, $handle);

            throw_unless(
                $form,
                new NotFoundHttpException("Form [$handle] not found.")
            );

            return $form;
        });
    }

    private function needsFormBinding(?Route $route): bool
    {
        if (! $route) {
            return false;
        }

        if ($this->isCpOrApiRoute($route)) {
            return true;
        }

        if ($this->isFrontendBindingEnabled()) {
            return true;
        }

        if ($route) {
            return Str::startsWith($route->uri(), config('statamic.routes.action').'/forms/');
        }

        return false;
    }

    private function isFrontendBindingEnabled()
    {
        return config('statamic.routes.bindings', false);
    }

    private function isApiRoute(Route $route)
    {
        $api = Str::ensureRight(config('statamic.api.route'), '/');

        if ($api === '/') {
            return true;
        }

        return Str::startsWith($route->uri(), $api);
    }

    private function isCpRoute(Route $route)
    {
        $cp = Str::ensureRight(config('statamic.cp.route'), '/');

        if ($cp === '/') {
            return true;
        }

        return Str::startsWith($route->uri(), $cp);
    }

    private function isCpOrApiRoute(Route $route)
    {
        return $this->isCpRoute($route) || $this->isApiRoute($route);
    }
}
