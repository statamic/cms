<?php

namespace Tests\API;

use Illuminate\Support\Facades\Storage;
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
    }

    /** @test */
    public function config_can_enable_all_assets_endpoints()
    {
        Facades\AssetContainer::make('main')->disk('test')->save();
        Facades\AssetContainer::make('avatars')->disk('test')->save();

        Facades\Config::set('statamic.api.endpoints.assets', true);

        $this->assertEndpointSuccessful('/api/assets/main');
        $this->assertEndpointSuccessful('/api/assets/avatars');

        $this->assertEndpointSuccessful('/api/assets/main/file.txt');
        $this->assertEndpointSuccessful('/api/assets/avatars/file.txt');
    }

    /** @test */
    public function config_can_disable_all_assets_endpoints()
    {
        Facades\AssetContainer::make('main')->disk('test')->save();
        Facades\AssetContainer::make('avatars')->disk('test')->save();

        Facades\Config::set('statamic.api.endpoints.assets', false);

        $this->assertEndpointNotFound('/api/assets/main');
        $this->assertEndpointNotFound('/api/assets/avatars');

        $this->assertEndpointNotFound('/api/assets/main/file.txt');
        $this->assertEndpointNotFound('/api/assets/avatars/file.txt');
    }

    /** @test */
    public function config_can_disable_some_assets_endpoints()
    {
        Facades\AssetContainer::make('main')->disk('test')->save();
        Facades\AssetContainer::make('avatars')->disk('test')->save();

        Facades\Config::set('statamic.api.endpoints.assets', ['avatars']);

        $this->assertEndpointNotFound('/api/assets/main');
        $this->assertEndpointSuccessful('/api/assets/avatars');

        $this->assertEndpointNotFound('/api/assets/main/file.txt');
        $this->assertEndpointSuccessful('/api/assets/avatars/file.txt');
    }

    /** @test */
    public function config_can_enable_all_entries_endpoints()
    {
        Facades\Collection::make('pages')->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.endpoints.entries', true);

        $this->assertEndpointSuccessful('/api/collections/pages/entries');
        $this->assertEndpointSuccessful('/api/collections/articles/entries');

        $this->assertEndpointSuccessful('/api/collections/pages/entries/about');
        $this->assertEndpointSuccessful('/api/collections/articles/entries/dance');
    }

    /** @test */
    public function config_can_disable_all_entries_endpoints()
    {
        Facades\Collection::make('pages')->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.endpoints.entries', false);

        $this->assertEndpointNotFound('/api/collections/pages/entries');
        $this->assertEndpointNotFound('/api/collections/articles/entries');

        $this->assertEndpointNotFound('/api/collections/pages/entries/about');
        $this->assertEndpointNotFound('/api/collections/articles/entries/dance');
    }

    /** @test */
    public function config_can_disable_some_entries_endpoints()
    {
        Facades\Collection::make('pages')->save();
        Facades\Collection::make('articles')->save();
        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->save();
        Facades\Entry::make()->collection('articles')->id('dance')->slug('dance')->save();

        Facades\Config::set('statamic.api.endpoints.entries', ['articles']);

        $this->assertEndpointNotFound('/api/collections/pages/entries');
        $this->assertEndpointSuccessful('/api/collections/articles/entries');

        $this->assertEndpointNotFound('/api/collections/pages/entries/about');
        $this->assertEndpointSuccessful('/api/collections/articles/entries/dance');
    }

    /** @test */
    public function config_can_enable_all_terms_endpoints()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();

        Facades\Config::set('statamic.api.endpoints.taxonomy-terms', true);

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms');
        $this->assertEndpointSuccessful('/api/taxonomies/colours/terms');

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms/dance');
        $this->assertEndpointSuccessful('/api/taxonomies/colours/terms/red');
    }

    /** @test */
    public function config_can_disable_all_terms_endpoints()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();

        Facades\Config::set('statamic.api.endpoints.taxonomy-terms', false);

        $this->assertEndpointNotFound('/api/taxonomies/topics/terms');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms');

        $this->assertEndpointNotFound('/api/taxonomies/topics/terms/dance');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms/red');
    }

    /** @test */
    public function config_can_disable_some_terms_endpoints()
    {
        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('colours')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();
        Facades\Term::make()->taxonomy('colours')->inDefaultLocale()->slug('red')->data([])->save();

        Facades\Config::set('statamic.api.endpoints.taxonomy-terms', ['topics']);

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms');

        $this->assertEndpointSuccessful('/api/taxonomies/topics/terms/dance');
        $this->assertEndpointNotFound('/api/taxonomies/colours/terms/red');
    }

    /** @test */
    public function config_can_enable_all_globals_endpoints()
    {
        $this->makeGlobalSet('settings')->save();
        $this->makeGlobalSet('social')->save();

        Facades\Config::set('statamic.api.endpoints.globals', true);

        $this->assertEndpointSuccessful('/api/globals');
        $this->assertEndpointDataCount('/api/globals', 2);

        $this->assertEndpointSuccessful('/api/globals/settings');
        $this->assertEndpointSuccessful('/api/globals/social');
    }

    /** @test */
    public function config_can_disable_all_globals_endpoints()
    {
        $this->makeGlobalSet('settings')->save();
        $this->makeGlobalSet('social')->save();

        Facades\Config::set('statamic.api.endpoints.globals', false);

        $this->assertEndpointNotFound('/api/globals');
        $this->assertEndpointNotFound('/api/globals/settings');
        $this->assertEndpointNotFound('/api/globals/social');
    }

    /** @test */
    public function config_can_disable_some_globals_endpoints()
    {
        $this->makeGlobalSet('settings')->save();
        $this->makeGlobalSet('social')->save();

        Facades\Config::set('statamic.api.endpoints.globals', ['social']);

        $this->assertEndpointSuccessful('/api/globals');
        $this->assertEndpointDataCount('/api/globals', 1);
        $this->assertEndpointDataHasJson('/api/globals', ['handle' => 'social']);

        $this->assertEndpointNotFound('/api/globals/settings');
        $this->assertEndpointSuccessful('/api/globals/social');
    }

    /** @test */
    public function config_can_enable_all_forms_endpoints()
    {
        Facades\Form::make('contact')->save();
        Facades\Form::make('survey')->save();

        Facades\Config::set('statamic.api.endpoints.forms', true);

        $this->assertEndpointSuccessful('/api/forms');
        $this->assertEndpointDataCount('/api/forms', 2);

        $this->assertEndpointSuccessful('/api/forms/contact');
        $this->assertEndpointSuccessful('/api/forms/survey');
    }

    /** @test */
    public function config_can_disable_all_forms_endpoints()
    {
        Facades\Form::make('contact')->save();
        Facades\Form::make('survey')->save();

        Facades\Config::set('statamic.api.endpoints.forms', false);

        $this->assertEndpointNotFound('/api/forms');
        $this->assertEndpointNotFound('/api/forms/contact');
        $this->assertEndpointNotFound('/api/forms/survey');
    }

    /** @test */
    public function config_can_disable_some_forms_endpoints()
    {
        Facades\Form::make('contact')->save();
        Facades\Form::make('survey')->save();

        Facades\Config::set('statamic.api.endpoints.forms', ['survey']);

        $this->assertEndpointSuccessful('/api/forms');
        $this->assertEndpointDataCount('/api/forms', 1);
        $this->assertEndpointDataHasJson('/api/forms', ['handle' => 'survey']);

        $this->assertEndpointNotFound('/api/forms/contact');
        $this->assertEndpointSuccessful('/api/forms/survey');
    }

    /** @test */
    public function config_can_enable_all_users_endpoints()
    {
        Facades\User::make()->id('one')->save();

        Facades\Config::set('statamic.api.endpoints.users', true);

        $this->assertEndpointSuccessful('/api/users');
        $this->assertEndpointDataCount('/api/users', 1);

        $this->assertEndpointSuccessful('/api/users/one');
    }

    /** @test */
    public function config_can_disable_all_users_endpoints()
    {
        Facades\User::make()->id('one')->save();

        Facades\Config::set('statamic.api.endpoints.users', false);

        $this->assertEndpointNotFound('/api/users');
        $this->assertEndpointNotFound('/api/users/one');
    }

    private function makeGlobalSet($handle)
    {
        $set = Facades\GlobalSet::make()->handle($handle);

        $set->addLocalization(
            $set->makeLocalization('en')->data([])
        );

        return $set;
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
