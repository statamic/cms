<?php

namespace Statamic\Providers;

use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        $this->bindEntries();
        $this->bindCollections();
        $this->bindTerms();
        $this->bindTaxonomies();
        $this->bindSites();
        $this->bindRevisions();
        $this->bindForms();
    }

    protected function bindEntries()
    {
        Route::bind('entry', function ($entry, $route) {
            abort_if(
                ! ($entry = Entry::find($entry))
                || $entry->collection() !== $route->parameter('collection')
            , 404);

            return $entry;
        });
    }

    protected function bindCollections()
    {
        Route::bind('collection', function ($collection) {
            abort_unless($collection = Collection::findByHandle($collection), 404);
            return $collection;
        });
    }

    protected function bindTaxonomies()
    {
        Route::bind('taxonomy', function ($taxonomy) {
            abort_unless($taxonomy = Taxonomy::findByHandle($taxonomy), 404);
            return $taxonomy;
        });
    }

    protected function bindTerms()
    {
        Route::bind('term', function ($term, $route) {
            $id = $route->parameter('taxonomy')->handle() . '::' . $term;
            abort_if(
                ! ($term = Term::find($id)->in($route->parameter('site')))
                || $term->taxonomy() !== $route->parameter('taxonomy')
            , 404);

            return $term;
        });
    }

    protected function bindSites()
    {
        Route::bind('site', function ($site) {
            abort_unless($site = Site::get($site), 404);
            return $site;
        });
    }

    protected function bindRevisions()
    {
        Route::bind('revision', function ($revision, $route) {
            if ($route->hasParameter('entry')) {
                $content = $route->parameter('entry');
            } elseif ($route->hasParameter('term')) {
                $content = $route->parameter('term');
            } else {
                abort(404);
            }

            abort_unless($revision = $content->revision($revision), 404);

            return $revision;
        });
    }

    protected function bindForms()
    {
        Route::bind('form', function ($form) {
            abort_unless($form = Form::find($form), 404);
            return $form;
        });
    }
}
