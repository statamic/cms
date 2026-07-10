<?php

namespace Tests\API;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        Storage::fake('test');
        Storage::disk('test')->put('file.txt', '');

        Facades\Form::all()->each->delete();
    }

    #[Test]
    public function config_can_enable_all_collections_resources()
    {
        Facades\Collection::make('pages')->structureContents(['expects_root' => false])->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.resources.collections', true);

        $this->assertEndpointSuccessful('/api/collections/pages/tree');
        $this->assertEndpointSuccessful('/api/collections/pages/entries');
        $this->assertEndpointSuccessful('/api/collections/articles/entries');

        $this->assertEndpointSuccessful('/api/collections/pages/entries/about');
        $this->assertEndpointSuccessful('/api/collections/articles/entries/dance');
    }

    #[Test]
    public function config_can_disable_all_collections_resources()
    {
        Facades\Collection::make('pages')->structureContents(['expects_root' => false])->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.resources.collections', false);

        $this->assertEndpointNotFound('/api/collections/pages/tree');
        $this->assertEndpointNotFound('/api/collections/pages/entries');
        $this->assertEndpointNotFound('/api/collections/articles/entries');

        $this->assertEndpointNotFound('/api/collections/pages/entries/about');
        $this->assertEndpointNotFound('/api/collections/articles/entries/dance');
    }

    #[Test]
    public function config_can_disable_resources_with_null()
    {
        // Exact same test as above, but using null instead of false. It would
        // be null if the user had their own config array with only a subset
        // of resources. Any missing resources would come through as null.

        Facades\Collection::make('pages')->structureContents(['expects_root' => false])->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.resources.collections', null);

        $this->assertEndpointNotFound('/api/collections/pages/tree');
        $this->assertEndpointNotFound('/api/collections/pages/entries');
        $this->assertEndpointNotFound('/api/collections/articles/entries');

        $this->assertEndpointNotFound('/api/collections/pages/entries/about');
        $this->assertEndpointNotFound('/api/collections/articles/entries/dance');
    }

    #[Test]
    public function config_can_disable_resources_with_unexpected_value()
    {
        // Exact same test as above, but using a weird string instead of false.
        // It would
        // be null if the user had their own config array with only a subset
        // of resources. Any missing resources would come through as null.

        Facades\Collection::make('pages')->structureContents(['expects_root' => false])->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.resources.collections', 'we only want arrays or booleans');

        $this->assertEndpointNotFound('/api/collections/pages/tree');
        $this->assertEndpointNotFound('/api/collections/pages/entries');
        $this->assertEndpointNotFound('/api/collections/articles/entries');

        $this->assertEndpointNotFound('/api/collections/pages/entries/about');
        $this->assertEndpointNotFound('/api/collections/articles/entries/dance');
    }

    #[Test]
    public function config_can_enable_some_collections_resources()
    {
        Facades\Collection::make('pages')->structureContents(['expects_root' => false])->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.resources.collections', ['pages']);

        $this->assertEndpointSuccessful('/api/collections/pages/tree');
        $this->assertEndpointSuccessful('/api/collections/pages/entries');
        $this->assertEndpointNotFound('/api/collections/articles/entries');

        $this->assertEndpointSuccessful('/api/collections/pages/entries/about');
        $this->assertEndpointNotFound('/api/collections/articles/entries/dance');
    }

    #[Test]
    public function config_can_enable_all_navs_resources()
    {
        $this->makeNav('footer')->save();
        $this->makeNav('docs')->save();

        Facades\Config::set('statamic.api.resources.navs', true);

        $this->assertEndpointSuccessful('/api/navs/footer/tree');
        $this->assertEndpointSuccessful('/api/navs/docs/tree');
    }

    #[Test]
    public function config_can_disable_all_navs_resources()
    {
        $this->makeNav('footer')->save();
        $this->makeNav('docs')->save();

        Facades\Config::set('statamic.api.resources.navs', false);

        $this->assertEndpointNotFound('/api/navs/footer/tree');
        $this->assertEndpointNotFound('/api/navs/docs/tree');
    }

    #[Test]
    public function config_can_enable_some_navs_resources()
    {
        $this->makeNav('footer')->save();
        $this->makeNav('docs')->save();

        Facades\Config::set('statamic.api.resources.navs', ['footer']);

        $this->assertEndpointSuccessful('/api/navs/footer/tree');
        $this->assertEndpointNotFound('/api/navs/docs/tree');
    }

    #[Test]
    public function config_can_enable_all_taxonomies_resources()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();

        Facades\Config::set('statamic.api.resources.taxonomies', true);

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms');
        $this->assertEndpointSuccessful('/api/taxonomies/colours/terms');

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms/dance');
        $this->assertEndpointSuccessful('/api/taxonomies/colours/terms/red');
    }

    #[Test]
    public function config_can_disable_all_taxonomies_resources()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();

        Facades\Config::set('statamic.api.resources.taxonomies', false);

        $this->assertEndpointNotFound('/api/taxonomies/topics/terms');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms');

        $this->assertEndpointNotFound('/api/taxonomies/topics/terms/dance');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms/red');
    }

    #[Test]
    public function config_can_enable_some_taxonomies_resources()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();

        Facades\Config::set('statamic.api.resources.taxonomies', ['topics']);

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms');

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms/dance');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms/red');
    }

    #[Test]
    public function config_can_enable_all_globals_resources()
    {
        $this->makeGlobalSet('settings')->save();
        $this->makeGlobalSet('social')->save();

        Facades\Config::set('statamic.api.resources.globals', true);

        $this->assertEndpointSuccessful('/api/globals');
        $this->assertEndpointDataCount('/api/globals', 2);

        $this->assertEndpointSuccessful('/api/globals/settings');
        $this->assertEndpointSuccessful('/api/globals/social');
    }

    #[Test]
    public function config_can_disable_all_globals_resources()
    {
        $this->makeGlobalSet('settings')->save();
        $this->makeGlobalSet('social')->save();

        Facades\Config::set('statamic.api.resources.globals', false);

        $this->assertEndpointNotFound('/api/globals');
        $this->assertEndpointNotFound('/api/globals/settings');
        $this->assertEndpointNotFound('/api/globals/social');
    }

    #[Test]
    public function config_can_enable_some_globals_resources()
    {
        $this->makeGlobalSet('settings')->save();
        $this->makeGlobalSet('social')->save();

        Facades\Config::set('statamic.api.resources.globals', ['social']);

        $this->assertEndpointSuccessful('/api/globals');
        $this->assertEndpointDataCount('/api/globals', 1);
        $this->assertEndpointDataHasJson('/api/globals', ['handle' => 'social']);

        $this->assertEndpointNotFound('/api/globals/settings');
        $this->assertEndpointSuccessful('/api/globals/social');
    }

    #[Test]
    public function config_can_enable_all_assets_resources()
    {
        Facades\AssetContainer::make('main')->disk('test')->save();
        Facades\AssetContainer::make('avatars')->disk('test')->save();

        Facades\Config::set('statamic.api.resources.assets', true);

        $this->assertEndpointSuccessful('/api/assets/main');
        $this->assertEndpointSuccessful('/api/assets/avatars');

        $this->assertEndpointSuccessful('/api/assets/main/file.txt');
        $this->assertEndpointSuccessful('/api/assets/avatars/file.txt');
    }

    #[Test]
    public function config_can_disable_all_assets_resources()
    {
        Facades\AssetContainer::make('main')->disk('test')->save();
        Facades\AssetContainer::make('avatars')->disk('test')->save();

        Facades\Config::set('statamic.api.resources.assets', false);

        $this->assertEndpointNotFound('/api/assets/main');
        $this->assertEndpointNotFound('/api/assets/avatars');

        $this->assertEndpointNotFound('/api/assets/main/file.txt');
        $this->assertEndpointNotFound('/api/assets/avatars/file.txt');
    }

    #[Test]
    public function config_can_enable_some_assets_resources()
    {
        Facades\AssetContainer::make('main')->disk('test')->save();
        Facades\AssetContainer::make('avatars')->disk('test')->save();

        Facades\Config::set('statamic.api.resources.assets', ['avatars']);

        $this->assertEndpointNotFound('/api/assets/main');
        $this->assertEndpointSuccessful('/api/assets/avatars');

        $this->assertEndpointNotFound('/api/assets/main/file.txt');
        $this->assertEndpointSuccessful('/api/assets/avatars/file.txt');
    }

    #[Test]
    public function config_can_enable_all_forms_resources()
    {
        Facades\Form::make('contact')->save();
        Facades\Form::make('survey')->save();

        Facades\Config::set('statamic.api.resources.forms', true);

        $this->assertEndpointSuccessful('/api/forms');
        $this->assertEndpointDataCount('/api/forms', 2);

        $this->assertEndpointSuccessful('/api/forms/contact');
        $this->assertEndpointSuccessful('/api/forms/survey');
    }

    #[Test]
    public function config_can_disable_all_forms_resources()
    {
        Facades\Form::make('contact')->save();
        Facades\Form::make('survey')->save();

        Facades\Config::set('statamic.api.resources.forms', false);

        $this->assertEndpointNotFound('/api/forms');
        $this->assertEndpointNotFound('/api/forms/contact');
        $this->assertEndpointNotFound('/api/forms/survey');
    }

    #[Test]
    public function config_can_enable_some_forms_resources()
    {
        Facades\Form::make('contact')->save();
        Facades\Form::make('survey')->save();

        Facades\Config::set('statamic.api.resources.forms', ['survey']);

        $this->assertEndpointSuccessful('/api/forms');
        $this->assertEndpointDataCount('/api/forms', 1);
        $this->assertEndpointDataHasJson('/api/forms', ['handle' => 'survey']);

        $this->assertEndpointNotFound('/api/forms/contact');
        $this->assertEndpointSuccessful('/api/forms/survey');
    }

    #[Test]
    public function config_can_enable_all_users()
    {
        Facades\User::make()->id('one')->save();

        Facades\Config::set('statamic.api.resources.users', true);

        $this->assertEndpointSuccessful('/api/users');
        $this->assertEndpointDataCount('/api/users', 1);

        $this->assertEndpointSuccessful('/api/users/one');
    }

    #[Test]
    public function config_can_disable_all_users()
    {
        Facades\User::make()->id('one')->save();

        Facades\Config::set('statamic.api.resources.users', false);

        $this->assertEndpointNotFound('/api/users');
        $this->assertEndpointNotFound('/api/users/one');
    }

    #[Test]
    public function config_can_enable_all_collection_entries_by_term()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();
        Facades\Collection::make('pages')->taxonomies(['topics', 'colours'])->save();
        Facades\Collection::make('articles')->taxonomies(['topics', 'colours'])->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->data(['topics' => 'dance'])->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->data(['topics' => 'dance', 'colours' => 'red'])->save();

        Facades\Config::set('statamic.api.resources.taxonomies', true);
        Facades\Config::set('statamic.api.resources.collections', true);

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms/dance/entries');
        $this->assertEndpointDataCount('/api/taxonomies/topics/terms/dance/entries', 2);
        $this->assertEndpointSuccessful('/api/taxonomies/colours/terms/red/entries');
        $this->assertEndpointDataCount('/api/taxonomies/colours/terms/red/entries', 1);
    }

    #[Test]
    public function config_can_disable_all_collection_entries_by_term()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();
        Facades\Collection::make('pages')->taxonomies(['topics', 'colours'])->save();
        Facades\Collection::make('articles')->taxonomies(['topics', 'colours'])->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->data(['topics' => 'dance'])->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->data(['topics' => 'dance', 'colours' => 'red'])->save();

        Facades\Config::set('statamic.api.resources.taxonomies', false);
        Facades\Config::set('statamic.api.resources.collections', true);

        $this->assertEndpointNotFound('/api/taxonomies/topics/terms/dance/entries');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms/red/entries');
    }

    #[Test]
    public function config_can_enable_some_collection_entries_by_term()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();
        Facades\Collection::make('pages')->taxonomies(['topics', 'colours'])->save();
        Facades\Collection::make('articles')->taxonomies(['topics', 'colours'])->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->data(['topics' => 'dance'])->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->data(['topics' => 'dance', 'colours' => 'red'])->save();

        Facades\Config::set('statamic.api.resources.taxonomies', ['topics']);
        Facades\Config::set('statamic.api.resources.collections', ['pages']);

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms/dance/entries');
        $this->assertEndpointDataCount('/api/taxonomies/topics/terms/dance/entries', 1);
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms/red/entries');
    }

    private function makeGlobalSet($handle)
    {
        $set = Facades\GlobalSet::make()->handle($handle);
        $set->save();

        $set->in('en')->data([])->save();

        return $set;
    }

    private function makeNav($handle)
    {
        $nav = Facades\Nav::make($handle);

        $nav->makeTree('en', [])->save();

        return $nav;
    }

    private function assertEndpointNotFound($endpoint)
    {
        $this
            ->get($endpoint)
            ->assertNotFound()
            ->assertJson(['message' => 'Not found.']);
    }

    private function assertEndpointSuccessful($endpoint)
    {
        $this
            ->get($endpoint)
            ->assertSuccessful()
            ->assertJson(['data' => []]);
    }

    private function assertEndpointDataCount($endpoint, $count)
    {
        $response = $this
            ->get($endpoint)
            ->assertSuccessful()
            ->assertJson(['data' => []]);

        $this->assertCount($count, $response->getData()->data);
    }

    private function assertEndpointDataHasJson($endpoint, $json)
    {
        $response = $this
            ->get($endpoint)
            ->assertSuccessful()
            ->assertJson(['data' => [$json]]);
    }
}
