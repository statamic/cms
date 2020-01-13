<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;

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
        $this->bindGlobalSets();
        $this->bindSites();
        $this->bindRevisions();
        $this->bindForms();
        $this->bindUsers();
    }

    protected function bindEntries()
    {
        Route::bind('entry', function ($handle, $route) {
            throw_if(
                ! ($entry = Entry::find($handle)) || $entry->collection() !== $route->parameter('collection'),
                new NotFoundHttpException("Entry [$handle] not found.")
            );

            return $entry;
        });
    }

    protected function bindCollections()
    {
        Route::bind('collection', function ($handle) {
            throw_unless(
                $collection = Collection::findByHandle($handle),
                new NotFoundHttpException("Collection [$handle] not found.")
            );

            return $collection;
        });
    }

    protected function bindTaxonomies()
    {
        Route::bind('taxonomy', function ($handle) {
            throw_unless(
                $taxonomy = Taxonomy::findByHandle($handle),
                new NotFoundHttpException("Taxonomy [$handle] not found.")
            );

            return $taxonomy;
        });
    }

    protected function bindTerms()
    {
        Route::bind('term', function ($handle, $route) {
            $id = $route->parameter('taxonomy')->handle() . '::' . $handle;

            throw_if(
                ! ($term = Term::find($id)->in($route->parameter('site'))) || $term->taxonomy() !== $route->parameter('taxonomy'),
                new NotFoundHttpException("Taxonomy [$handle] not found.")
            );

            return $term;
        });
    }

    protected function bindGlobalSets()
    {
        Route::bind('global', function ($handle) {
            throw_unless(
                $globalSet = GlobalSet::findByHandle($handle),
                new NotFoundHttpException("Global set [$handle] not found.")
            );

            return $globalSet;
        });
    }

    protected function bindSites()
    {
        Route::bind('site', function ($handle) {
            throw_unless(
                $site = Site::get($handle),
                new NotFoundHttpException("Site [$handle] not found.")
            );

            return $site;
        });
    }

    protected function bindRevisions()
    {
        Route::bind('revision', function ($reference, $route) {
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
        Route::bind('form', function ($handle) {
            throw_unless(
                $form = Form::find($handle),
                new NotFoundHttpException("Form [$form] not found.")
            );

            return $form;
        });
    }

    protected function bindUsers()
    {
        Route::bind('user', function ($handle, $route) {
            throw_unless(
                $user = User::find($handle),
                new NotFoundHttpException("User [$handle] not found.")
            );

            return $user;
        });
    }
}
