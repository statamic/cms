<?php

namespace Tests\Routing;

use Closure;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Revisions\Revision;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades;
use Statamic\Sites\Site;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RouteBindingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $route;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::middleware(SubstituteBindings::class)->group(function () {
                collect([
                    // entries and collections
                    'cp/custom/collections/{collection}',
                    'cp/custom/collections/{collection}/entries/{entry}',
                    'api/custom/collections/{collection}',
                    'api/custom/collections/{collection}/entries/{entry}',
                    'custom/collections/title/{collection:title}',
                    'custom/collections/{collection}',
                    'custom/entries/slug/{entry:slug}',
                    'custom/entries/{entry}',
                    'custom/collections/{collection}/entries/{entry}',

                    // terms and taxonomies
                    'cp/custom/taxonomies/{taxonomy}',
                    'cp/custom/taxonomies/{taxonomy}/terms/{term}',
                    'api/custom/taxonomies/{taxonomy}',
                    'api/custom/taxonomies/{taxonomy}/terms/{term}',
                    'custom/taxonomies/title/{taxonomy:title}',
                    'custom/taxonomies/{taxonomy}',
                    'custom/terms/slug/{term:slug}',
                    'custom/terms/{term}',
                    'custom/taxonomies/{taxonomy}/terms/{term}',

                    // assets and containers (more below)
                    'cp/custom/containers/{asset_container}',
                    'api/custom/containers/{asset_container}',
                    'custom/containers/title/{asset_container:title}',
                    'custom/containers/{asset_container}',
                    'custom/assets/alt/{asset_container}/assets/{asset:alt}',

                    // globals
                    'cp/custom/globals/{global}',
                    'api/custom/globals/{global}',
                    'custom/globals/title/{global:title}',
                    'custom/globals/{global}',

                    // sites
                    'cp/custom/sites/{site}',
                    'api/custom/sites/{site}',
                    'custom/sites/locale/{site:locale}',
                    'custom/sites/{site}',

                    // revisions
                    'cp/custom/entries/{collection}/{entry}/revisions/{revision}',
                    'cp/custom/terms/{taxonomy}/{term}/revisions/{revision}',
                    'cp/custom/revisions/{revision}',
                    'api/custom/entries/{collection}/{entry}/revisions/{revision}',
                    'api/custom/terms/{taxonomy}/{term}/revisions/{revision}',
                    'api/custom/revisions/{revision}',
                    'custom/entries/{collection}/{entry}/revisions/{revision}',
                    'custom/terms/{taxonomy}/{term}/revisions/{revision}',
                    'custom/revisions/{revision}',

                    // forms
                    'cp/custom/forms/{form}',
                    'api/custom/forms/{form}',
                    'custom/forms/title/{form:title}',
                    'custom/forms/{form}',
                ])->each(fn ($uri) => Route::get($uri, fn () => $this->route()));

                // assets are special because they have a path
                Route::get('cp/custom/assets/{asset_container}/assets/{asset}', fn () => $this->route())->where('asset', '.*');
                Route::get('api/custom/assets/{asset_container}/assets/{asset}', fn () => $this->route())->where('asset', '.*');
                Route::get('custom/assets/{asset_container}/assets/{asset}', fn () => $this->route())->where('asset', '.*');
            });
        });
    }

    private function route()
    {
        $this->route = request()->route();
    }

    public function bindingsEnabled($app)
    {
        $app['config']->set('statamic.routes.bindings', true);
    }

    public function bindingsDisabled($app)
    {
        $app['config']->set('statamic.routes.bindings', false);
    }

    #[Test]
    #[DefineEnvironment('bindingsEnabled')]
    #[DataProvider('statamicRouteProvider')]
    public function binds_route_parameters_in_statamic_routes(
        $uri,
        $expectationCallback = null
    ) {
        $this->setupContent();

        $response = $this->get($uri);

        if ($expectationCallback) {
            $response->assertOk();
            $this->assertTrue($expectationCallback(...$this->route->parameters()));
        } else {
            $response->assertNotFound();
        }
    }

    #[Test]
    #[DefineEnvironment('bindingsDisabled')]
    #[DataProvider('statamicRouteProvider')]
    public function binds_route_parameters_in_statamic_routes_with_bindings_disabled(
        $uri,
        $expectationCallback = null
    ) {
        $this->setupContent();

        $response = $this->get($uri);

        if ($expectationCallback) {
            $response->assertOk();
            $this->assertTrue($expectationCallback(...$this->route->parameters()));
        } else {
            $response->assertNotFound();
        }
    }

    #[Test]
    #[DefineEnvironment('bindingsEnabled')]
    #[DataProvider('frontendRouteProvider')]
    public function binds_route_parameters_in_frontend_routes(
        $uri,
        ?Closure $enabledCallback = null,
        ?Closure $disabledCallback = null,
    ) {
        $this->setupContent();

        $response = $this->get($uri);

        if ($enabledCallback) {
            $response->assertOk();
            $this->assertTrue($enabledCallback(...$this->route->parameters()));
        } else {
            $response->assertNotFound();
        }
    }

    #[Test]
    #[DefineEnvironment('bindingsDisabled')]
    #[DataProvider('frontendRouteProvider')]
    public function binds_route_parameters_in_frontend_routes_with_bindings_disabled(
        $uri,
        ?Closure $enabledCallback = null,
        ?Closure $disabledCallback = null,
    ) {
        $this->setupContent();

        $response = $this->get($uri);

        if ($disabledCallback) {
            $response->assertOk();
            $this->assertTrue($disabledCallback(...$this->route->parameters()));
        } else {
            $response->assertNotFound();
        }
    }

    private function setupContent()
    {
        Facades\Collection::make('blog')->title('The Blog')->save();
        $entry = EntryFactory::id('123')->slug('alfa')->collection('blog')->create();
        $entryRevision = $entry->makeRevision()->id('1');
        Facades\Revision::shouldReceive('whereKey')->with('collections/blog/en/123')->andReturn(collect(['1' => $entryRevision]));

        Facades\Taxonomy::make('tags')->title('Product Tags')->save();
        $term = tap(Facades\Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data([]))->save();
        $termRevision = $term->inDefaultLocale()->makeRevision()->id('2');
        Facades\Revision::shouldReceive('whereKey')->with('taxonomies/tags/en/bravo')->andReturn(collect(['2' => $termRevision]));

        Facades\AssetContainer::make('files')->disk('files')->title('The Files')->save();
        Storage::fake('files');
        Storage::disk('files')->put('foo/bar.txt', '');
        Storage::disk('files')->put('foo/.meta/bar.txt.yaml', "data:\n  alt: 'the alt text'");

        $set = Facades\GlobalSet::make('seo')->title('SEO Settings');
        $set->addLocalization($set->makeLocalization('en'));
        $set->save();

        Facades\Form::make('contact')->title('Contact Us')->save();
    }

    public static function statamicRouteProvider()
    {

        return [

            // entries and collections

            'cp collection' => [
                'cp/custom/collections/blog',
                function (Collection $collection) {
                    return $collection->handle() === 'blog';
                },
            ],

            'cp missing collection' => [
                'cp/custom/collections/invalid',
            ],

            'api collection' => [
                'api/custom/collections/blog',
                function (Collection $collection) {
                    return $collection->handle() === 'blog';
                },
            ],

            'api missing collection' => [
                'api/custom/collections/invalid',
            ],

            'cp collection and entry' => [
                'cp/custom/collections/blog/entries/123',
                function (Collection $collection, Entry $entry) {
                    return $collection->handle() === 'blog' && $entry->id() === '123';
                },
            ],

            'cp missing collection and entry' => [
                'cp/custom/collections/invalid/entries/123',
            ],

            'cp collection and missing entry' => [
                'cp/custom/collections/blog/entries/invalid',
            ],

            'api collection and entry' => [
                'api/custom/collections/blog/entries/123',
                function (Collection $collection, string $entry) {
                    return $collection->handle() === 'blog' && $entry === '123';
                },
            ],

            'api missing collection and entry' => [
                'api/custom/collections/invalid/entries/123',
            ],

            'api collection and missing entry' => [
                'api/custom/collections/blog/entries/invalid',
                function (Collection $collection, string $entry) {
                    return $collection->handle() === 'blog' && $entry === 'invalid';
                },
            ],

            // terms and taxonomies

            'cp taxonomy' => [
                'cp/custom/taxonomies/tags',
                function (Taxonomy $taxonomy) {
                    return $taxonomy->handle() === 'tags';
                },
            ],

            'cp missing taxonomy' => [
                'cp/custom/taxonomies/invalid',
            ],

            'api taxonomy' => [
                'api/custom/taxonomies/tags',
                function (Taxonomy $taxonomy) {
                    return $taxonomy->handle() === 'tags';
                },
            ],

            'api missing taxonomy' => [
                'api/custom/taxonomies/invalid',
            ],

            'cp taxonomy and term' => [
                'cp/custom/taxonomies/tags/terms/bravo',
                function (Taxonomy $taxonomy, Term $term) {
                    return $taxonomy->handle() === 'tags' && $term->id() === 'tags::bravo';
                },
            ],

            'cp missing taxonomy and term' => [
                'cp/custom/taxonomies/invalid/terms/bravo',
            ],

            'cp taxonomy and missing term' => [
                'cp/custom/taxonomies/tags/terms/invalid',
            ],

            'api taxonomy and term' => [
                'api/custom/taxonomies/tags/terms/bravo',
                function (Taxonomy $taxonomy, string $term) {
                    return $taxonomy->handle() === 'tags' && $term === 'bravo';
                },
            ],

            'api missing taxonomy and term' => [
                'api/custom/taxonomies/invalid/terms/bravo',
            ],

            'api taxonomy and missing term' => [
                'api/custom/taxonomies/tags/terms/invalid',
                function (Taxonomy $taxonomy, string $term) {
                    return $taxonomy->handle() === 'tags' && $term === 'invalid';
                },
            ],

            // assets and containers

            'cp container' => [
                'cp/custom/containers/files',
                function (AssetContainer $asset_container) {
                    return $asset_container->handle() === 'files';
                },
            ],

            'cp missing container' => [
                'cp/custom/containers/invalid',
            ],

            'api container' => [
                'api/custom/containers/files',
                function (AssetContainer $asset_container) {
                    return $asset_container->handle() === 'files';
                },
            ],

            'api missing container' => [
                'api/custom/containers/invalid',
            ],

            'cp container and asset' => [
                'cp/custom/assets/files/assets/foo/bar.txt',
                function (AssetContainer $asset_container, Asset $asset) {
                    return $asset_container->handle() === 'files' && $asset->id() === 'files::foo/bar.txt';
                },
            ],

            'cp missing container and asset' => [
                'cp/custom/assets/invalid/assets/foo/bar.txt',
            ],

            'cp container and missing asset' => [
                'cp/custom/assets/files/assets/invalid',
            ],

            'api container and asset' => [
                'api/custom/assets/files/assets/foo/bar.txt',
                function (AssetContainer $asset_container, Asset $asset) {
                    return $asset_container->handle() === 'files' && $asset->id() === 'files::foo/bar.txt';
                },
            ],

            'api missing container and asset' => [
                'api/custom/assets/invalid/assets/foo/bar.txt',
            ],

            'api container and missing asset' => [
                'api/custom/assets/files/assets/foo/invalid.txt',
            ],

            // globals

            'cp global' => [
                'cp/custom/globals/seo',
                function (Variables $global) {
                    return $global->handle() === 'seo';
                },
            ],

            'cp missing global' => [
                'cp/custom/globals/invalid',
            ],

            'api global' => [
                'api/custom/globals/seo',
                function (Variables $global) {
                    return $global->handle() === 'seo';
                },
            ],

            'api missing global' => [
                'api/custom/globals/invalid',
            ],

            // sites

            'cp site' => [
                'cp/custom/sites/en',
                function (Site $site) {
                    return $site->handle() === 'en';
                },
            ],

            'cp missing site' => [
                'cp/custom/sites/invalid',
            ],

            'api site' => [
                'api/custom/sites/en',
                function (Site $site) {
                    return $site->handle() === 'en';
                },
            ],

            'api missing site' => [
                'api/custom/sites/invalid',
            ],

            // revisions

            'cp entry revision' => [
                'cp/custom/entries/blog/123/revisions/1',
                function (Collection $collection, Entry $entry, Revision $revision) {
                    return $collection->handle() === 'blog' && $entry->id() === '123' && $revision->id() === '1';
                },
            ],

            'cp entry missing revision' => [
                'cp/custom/entries/blog/123/revisions/invalid',
            ],

            'cp term revision' => [
                'cp/custom/terms/tags/bravo/revisions/2',
                function (Taxonomy $taxonomy, Term $term, Revision $revision) {
                    return $taxonomy->handle() === 'tags' && $term->id() === 'tags::bravo' && $revision->id() === '2';
                },
            ],

            'cp term missing revision' => [
                'cp/custom/terms/tags/bravo/revisions/invalid',
            ],

            'cp invalid content revision' => [
                'cp/custom/revisions/1',
            ],

            'api entry revision' => [
                'api/custom/entries/blog/123/revisions/1',
                function (Collection $collection, string $entry, string $revision) {
                    return $collection->handle() === 'blog' && $entry === '123' && $revision === '1';
                },
            ],

            'api entry missing revision' => [
                'api/custom/entries/blog/123/revisions/invalid',
                function (Collection $collection, string $entry, string $revision) {
                    return $collection->handle() === 'blog' && $entry === '123' && $revision === 'invalid';
                },
            ],

            'api term revision' => [
                'api/custom/terms/tags/bravo/revisions/2',
                function (Taxonomy $taxonomy, string $term, $revision) {
                    return $taxonomy->handle() === 'tags' && $term === 'bravo' && $revision === '2';
                },
            ],

            'api term missing revision' => [
                'api/custom/terms/tags/bravo/revisions/invalid',
                function (Taxonomy $taxonomy, string $term, string $revision) {
                    return $taxonomy->handle() === 'tags' && $term === 'bravo' && $revision === 'invalid';
                },
            ],

            'api invalid content revision' => [
                'api/custom/revisions/1',
                function (string $revision) {
                    return $revision === '1';
                },
            ],

            // forms

            'cp form' => [
                'cp/custom/forms/contact',
                function (Form $form) {
                    return $form->handle() === 'contact';
                },
            ],

            'cp missing form' => [
                'cp/custom/forms/invalid',
            ],

            'api form' => [
                'cp/custom/forms/contact',
                function (Form $form) {
                    return $form->handle() === 'contact';
                },
            ],

            'api missing form' => [
                'api/custom/forms/invalid',
            ],

        ];
    }

    public static function frontendRouteProvider()
    {
        return [

            // entries and collections

            'collection' => [
                'custom/collections/blog',
                function (Collection $collection) {
                    return $collection->handle() === 'blog';
                },
                function (string $collection) {
                    return $collection === 'blog';
                },
            ],

            'collection with custom binding' => [
                'custom/collections/title/The%20Blog',
                function (Collection $collection) {
                    return $collection->handle() === 'blog';
                },
                function (string $collection) {
                    return $collection === 'The Blog';
                },
            ],

            'missing collection' => [
                'custom/collections/invalid',
                null,
                function (string $collection) {
                    return $collection === 'invalid';
                },
            ],

            'entry' => [
                'custom/entries/123',
                function (Entry $entry) {
                    return $entry->id() === '123';
                },
                function (string $entry) {
                    return $entry === '123';
                },
            ],

            'missing entry' => [
                'custom/entries/invalid',
                null,
                function (string $entry) {
                    return $entry === 'invalid';
                },
            ],

            'collection and entry' => [
                'custom/collections/blog/entries/123',
                function (Collection $collection, Entry $entry) {
                    return $collection->handle() === 'blog' && $entry->id() === '123';
                },
                function (string $collection, string $entry) {
                    return $collection === 'blog' && $entry === '123';
                },
            ],

            'missing collection and entry' => [
                'custom/collections/invalid/entries/123',
                null,
                function (string $collection, string $entry) {
                    return $collection === 'invalid' && $entry === '123';
                },
            ],

            'collection and missing entry' => [
                'custom/collections/blog/entries/invalid',
                null,
                function (string $collection, string $entry) {
                    return $collection === 'blog' && $entry === 'invalid';
                },
            ],

            'entry with custom binding' => [
                'custom/entries/slug/alfa',
                function (Entry $entry) {
                    return $entry->id() === '123';
                },
                function (string $entry) {
                    return $entry === 'alfa';
                },
            ],

            // terms and taxonomies

            'taxonomy' => [
                'custom/taxonomies/tags',
                function (Taxonomy $taxonomy) {
                    return $taxonomy->handle() === 'tags';
                },
                function (string $taxonomy) {
                    return $taxonomy === 'tags';
                },
            ],

            'taxonomy with custom binding' => [
                'custom/taxonomies/title/Product%20Tags',
                function (Taxonomy $taxonomy) {
                    return $taxonomy->handle() === 'tags';
                },
                function (string $taxonomy) {
                    return $taxonomy === 'Product Tags';
                },
            ],

            'missing taxonomy' => [
                'custom/taxonomies/invalid',
                null,
                function (string $taxonomy) {
                    return $taxonomy === 'invalid';
                },
            ],

            'term' => [
                'custom/terms/tags::bravo',
                function (Term $term) {
                    return $term->id() === 'tags::bravo';
                },
                function (string $term) {
                    return $term === 'tags::bravo';
                },
            ],

            'missing term' => [
                'custom/terms/tags::invalid',
                null,
                function (string $term) {
                    return $term === 'tags::invalid';
                },
            ],

            'taxonomy and term' => [
                'custom/taxonomies/tags/terms/bravo',
                function (Taxonomy $taxonomy, Term $term) {
                    return $taxonomy->handle() === 'tags' && $term->id() === 'tags::bravo';
                },
                function (string $taxonomy, string $term) {
                    return $taxonomy === 'tags' && $term === 'bravo';
                },
            ],

            'missing taxonomy and term' => [
                'custom/taxonomies/invalid/terms/bravo',
                null,
                function (string $taxonomy, string $term) {
                    return $taxonomy === 'invalid' && $term === 'bravo';
                },
            ],

            'taxonomy and missing term' => [
                'custom/taxonomies/tags/terms/invalid',
                null,
                function (string $taxonomy, string $term) {
                    return $taxonomy === 'tags' && $term === 'invalid';
                },
            ],

            'term with custom binding' => [
                'custom/terms/slug/bravo',
                function (Term $term) {
                    return $term->id() === 'tags::bravo';
                },
                function (string $term) {
                    return $term === 'bravo';
                },
            ],

            // assets and containers

            'container' => [
                'custom/containers/files',
                function (AssetContainer $asset_container) {
                    return $asset_container->handle() === 'files';
                },
                function (string $asset_container) {
                    return $asset_container === 'files';
                },
            ],

            'container with custom binding' => [
                'custom/containers/title/The%20Files',
                function (AssetContainer $asset_container) {
                    return $asset_container->handle() === 'files';
                },
                function (string $asset_container) {
                    return $asset_container === 'The Files';
                },
            ],

            'missing container' => [
                'custom/containers/invalid',
                null,
                function (string $asset_container) {
                    return $asset_container === 'invalid';
                },
            ],

            'container and asset' => [
                'custom/assets/files/assets/foo/bar.txt',
                function (AssetContainer $asset_container, Asset $asset) {
                    return $asset_container->handle() === 'files' && $asset->id() === 'files::foo/bar.txt';
                },
                function (string $asset_container, string $asset) {
                    return $asset_container === 'files' && $asset === 'foo/bar.txt';
                },
            ],

            'missing container and asset' => [
                'custom/assets/invalid/assets/foo/bar.txt',
                null,
                function (string $asset_container, string $asset) {
                    return $asset_container === 'invalid' && $asset === 'foo/bar.txt';
                },
            ],

            'container and missing asset' => [
                'custom/assets/files/assets/invalid',
                null,
                function (string $asset_container, string $asset) {
                    return $asset_container === 'files' && $asset === 'invalid';
                },
            ],

            'asset with custom binding' => [
                'custom/assets/alt/files/assets/the%20alt%20text',
                function (AssetContainer $asset_container, Asset $asset) {
                    return $asset_container->handle() === 'files' && $asset->id() === 'files::foo/bar.txt';
                },
                function (string $asset_container, string $asset) {
                    return $asset_container === 'files' && $asset === 'the alt text';
                },
            ],

            // globals

            'global' => [
                'custom/globals/seo',
                function (Variables $global) {
                    return $global->handle() === 'seo';
                },
                function (string $global) {
                    return $global === 'seo';
                },
            ],

            'global with custom binding' => [
                'custom/globals/title/SEO%20Settings',
                function (Variables $global) {
                    return $global->handle() === 'seo';
                },
                function (string $global) {
                    return $global === 'SEO Settings';
                },
            ],

            'missing global' => [
                'custom/globals/invalid',
                null,
                function (string $global) {
                    return $global === 'invalid';
                },
            ],

            // sites

            'site' => [
                'custom/sites/en',
                function (Site $site) {
                    return $site->handle() === 'en';
                },
                function (string $site) {
                    return $site === 'en';
                },
            ],

            'site with custom binding' => [
                'custom/sites/locale/en_US',
                function (Site $site) {
                    return $site->handle() === 'en';
                },
                function (string $site) {
                    return $site === 'en_US';
                },
            ],

            'missing site' => [
                'custom/sites/invalid',
                null,
                function (string $site) {
                    return $site === 'invalid';
                },
            ],

            // revisions

            'entry revision' => [
                'custom/entries/blog/123/revisions/1',
                function (Collection $collection, Entry $entry, Revision $revision) {
                    return $collection->handle() === 'blog' && $entry->id() === '123' && $revision->id() === '1';
                },
                function (string $collection, string $entry, string $revision) {
                    return $collection === 'blog' && $entry === '123' && $revision === '1';
                },
            ],

            'entry missing revision' => [
                'custom/entries/blog/123/revisions/invalid',
                null,
                function (string $collection, string $entry, string $revision) {
                    return $collection === 'blog' && $entry === '123' && $revision === 'invalid';
                },
            ],

            'term revision' => [
                'custom/terms/tags/bravo/revisions/2',
                function (Taxonomy $taxonomy, Term $term, Revision $revision) {
                    return $taxonomy->handle() === 'tags' && $term->id() === 'tags::bravo' && $revision->id() === '2';
                },
                function (string $taxonomy, string $term, string $revision) {
                    return $taxonomy === 'tags' && $term === 'bravo' && $revision === '2';
                },
            ],

            'term missing revision' => [
                'custom/terms/tags/bravo/revisions/invalid',
                null,
                function (string $taxonomy, string $term, string $revision) {
                    return $taxonomy === 'tags' && $term === 'bravo' && $revision === 'invalid';
                },
            ],

            'invalid content revision' => [
                'custom/revisions/1',
                null,
                function (string $revision) {
                    return $revision === '1';
                },
            ],

            // forms

            'form' => [
                'custom/forms/contact',
                function (Form $form) {
                    return $form->handle() === 'contact';
                },
                function (string $form) {
                    return $form === 'contact';
                },
            ],

            'form with custom binding' => [
                'custom/forms/title/Contact%20Us',
                function (Form $form) {
                    return $form->handle() === 'contact';
                },
                function (string $form) {
                    return $form === 'Contact Us';
                },
            ],

            'missing form' => [
                'custom/forms/invalid',
                null,
                function (string $form) {
                    return $form === 'invalid';
                },
            ],

        ];
    }

    #[Test]
    #[DefineEnvironment('bindingsDisabled')]
    #[DataProvider('bypassForBroadcastingProvider')]
    public function it_bypasses_binding_for_broadcasting($binding)
    {
        $this->bypassForBroadcasting($binding);
    }

    #[Test]
    #[DefineEnvironment('bindingsEnabled')]
    #[DataProvider('bypassForBroadcastingProvider')]
    public function it_bypasses_binding_for_broadcasting_with_bindings_enabled($binding)
    {
        $this->bypassForBroadcasting($binding);
    }

    public function bypassForBroadcasting(string $binding)
    {
        $binder = app(BindingRegistrar::class);

        // Evaluate the binding closure, passing in the value.
        // e.g. If you hit /something/{entry} then the closure will receive literally "entry" as the first argument.
        $value = call_user_func($binder->getBindingCallback($binding), 'test');

        // We want to make sure it just spits the value right back without any errors.
        $this->assertEquals('test', $value);
    }

    public static function bypassForBroadcastingProvider()
    {
        return collect([
            'collection',
            'entry',
            'taxonomy',
            'term',
            'asset_container',
            'asset',
            'global',
            'site',
            'revision',
            'form',
        ])->mapWithKeys(fn ($key) => [$key => [$key]])->all();
    }
}
