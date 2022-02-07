<?php

namespace Tests\API;

use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class APITest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function not_found_responses_are_formatted_with_json()
    {
        $this
            ->get('/api/blah')
            ->assertNotFound()
            ->assertJson(['message' => 'Not found.']);
    }

    /** @test */
    public function it_filters_published_entries_by_default()
    {
        Facades\Config::set('statamic.api.resources.collections', true);

        Facades\Collection::make('pages')->save();

        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->published(true)->save();
        Facades\Entry::make()->collection('pages')->id('dance')->slug('dance')->published(false)->save();
        Facades\Entry::make()->collection('pages')->id('nectar')->slug('nectar')->published(false)->save();

        $this->assertEndpointDataCount('/api/collections/pages/entries', 1);
        $this->assertEndpointDataCount('/api/collections/pages/entries?filter[status:is]=published', 1);
        $this->assertEndpointDataCount('/api/collections/pages/entries?filter[status:is]=draft', 2);
        $this->assertEndpointDataCount('/api/collections/pages/entries?filter[published:is]=true', 1);
        $this->assertEndpointDataCount('/api/collections/pages/entries?filter[published:is]=false', 2);

        $this->assertEndpointSuccessful('/api/collections/pages/entries/about');
        $this->assertEndpointNotFound('/api/collections/pages/entries/dance');
        $this->assertEndpointNotFound('/api/collections/pages/entries/nectar');
    }

    /** @test */
    public function it_filters_published_entries_in_collection_tree_route_by_default()
    {
        Facades\Config::set('statamic.api.resources.collections', true);

        Facades\Collection::make('pages')->structureContents(['root' => true])->save();

        Facades\Entry::make()->collection('pages')->id('one')->slug('one')->published(true)->save();
        Facades\Entry::make()->collection('pages')->id('two')->slug('two')->published(false)->save();
        Facades\Entry::make()->collection('pages')->id('three')->slug('three')->published(false)->save();

        Facades\Collection::find('pages')->structure()->makeTree('en', [
            ['entry' => 'one'],
            ['entry' => 'two'],
            ['entry' => 'three'],
        ])->save();

        $this->assertEndpointDataCount('/api/collections/pages/tree', 1);
        $this->assertEndpointDataCount('/api/collections/pages/tree?filter[status:is]=published', 1);
        $this->assertEndpointDataCount('/api/collections/pages/tree?filter[status:is]=draft', 2);
        $this->assertEndpointDataCount('/api/collections/pages/tree?filter[published:is]=true', 1);
        $this->assertEndpointDataCount('/api/collections/pages/tree?filter[published:is]=false', 2);
    }

    /** @test */
    public function it_filters_published_entries_on_term_entries_route_by_default()
    {
        Facades\Config::set('statamic.api.resources.collections', true);
        Facades\Config::set('statamic.api.resources.taxonomies', true);

        Facades\Taxonomy::make('topics')->save();

        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();

        Facades\Collection::make('pages')->taxonomies(['topics'])->save();

        Facades\Entry::make()->collection('pages')->slug('one')->published(true)->data(['topics' => 'dance'])->save();
        Facades\Entry::make()->collection('pages')->slug('two')->published(false)->data(['topics' => 'dance'])->save();
        Facades\Entry::make()->collection('pages')->slug('three')->published(false)->data(['topics' => 'dance'])->save();

        $this->assertEndpointDataCount('/api/taxonomies/topics/terms/dance/entries', 1);
        $this->assertEndpointDataCount('/api/taxonomies/topics/terms/dance/entries?filter[status:is]=published', 1);
        $this->assertEndpointDataCount('/api/taxonomies/topics/terms/dance/entries?filter[status:is]=draft', 2);
        $this->assertEndpointDataCount('/api/taxonomies/topics/terms/dance/entries?filter[published:is]=true', 1);
        $this->assertEndpointDataCount('/api/taxonomies/topics/terms/dance/entries?filter[published:is]=false', 2);
    }

    /** @test */
    public function it_filters_published_entries_in_nav_route_by_default()
    {
        Facades\Config::set('statamic.api.resources.navs', true);

        Facades\Collection::make('pages')->save();

        $nav = Facades\Nav::make('footer');
        $nav->makeTree('en', [
            ['entry' => 'one'],
            ['entry' => 'two'],
            ['entry' => 'three'],
            ['title' => 'Balki Bartokomous'],
        ])->save();
        $nav->save();

        Facades\Entry::make()->collection('pages')->id('one')->slug('one')->published(true)->save();
        Facades\Entry::make()->collection('pages')->id('two')->slug('two')->published(false)->save();
        Facades\Entry::make()->collection('pages')->id('three')->slug('three')->published(false)->save();

        $this->assertEndpointDataCount('/api/navs/footer/tree', 2);
    }

    /** @test */
    public function it_excludes_keys()
    {
        Facades\Config::set('statamic.api.resources.collections', true);
        Facades\Config::set('statamic.api.cache', false);

        Facades\Collection::make('pages')->save();

        Facades\Entry::make()->collection('pages')->id('dance')->slug('dance')->published(true)->save();

        $apiUrl = 'http://localhost/api/collections/pages/entries/dance';
        $editUrl = 'http://localhost/cp/collections/pages/entries/dance';

        $this
            ->get('/api/collections/pages/entries')
            ->assertJsonPath('data.0.api_url', $apiUrl)
            ->assertJsonPath('data.0.edit_url', $editUrl);

        $this
            ->get('/api/collections/pages/entries/dance')
            ->assertJsonPath('data.api_url', $apiUrl)
            ->assertJsonPath('data.edit_url', $editUrl);

        Facades\Config::set('statamic.api.excluded_keys', ['api_url', 'edit_url']);

        $this
            ->get('/api/collections/pages/entries')
            ->assertJsonPath('data.0.api_url', null)
            ->assertJsonPath('data.0.edit_url', null);

        $this
            ->get('/api/collections/pages/entries/dance')
            ->assertJsonPath('data.api_url', null)
            ->assertJsonPath('data.edit_url', null);
    }

    private function assertEndpointDataCount($endpoint, $count)
    {
        $response = $this
            ->get($endpoint)
            ->assertSuccessful()
            ->assertJson(['data' => []]);

        $this->assertCount($count, $response->getData()->data);
    }

    private function assertEndpointSuccessful($endpoint)
    {
        $this
            ->get($endpoint)
            ->assertSuccessful()
            ->assertJson(['data' => []]);
    }

    private function assertEndpointNotFound($endpoint)
    {
        $this
            ->get($endpoint)
            ->assertNotFound()
            ->assertJson(['message' => 'Not found.']);
    }
}
