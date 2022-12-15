<?php

namespace Tests\API;

use Facades\Statamic\CP\LivePreview;
use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
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

    /**
     * @test
     * @dataProvider entryNotFoundProvider
     */
    public function it_handles_not_found_entries($url, $requestShouldSucceed)
    {
        Facades\Config::set('statamic.api.resources.collections', true);

        Facades\Collection::make('pages')->save();
        Facades\Collection::make('articles')->save();

        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->published(true)->save();

        if ($requestShouldSucceed) {
            $this->assertEndpointSuccessful($url);
        } else {
            $this->assertEndpointNotFound($url);
        }
    }

    public function entryNotFoundProvider()
    {
        return [
            'valid entry id' => ['/api/collections/pages/entries/about', true],
            'invalid entry id' => ['/api/collections/pages/entries/dance', false],
            'valid entry id but wrong collection' => ['/api/collections/articles/entries/about', false],
        ];
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
    public function it_filters_by_taxonomy_terms()
    {
        Facades\Config::set('statamic.api.resources.collections', true);

        $this->makeTaxonomy('tags')->save();
        $this->makeTerm('tags', 'rad')->save();
        $this->makeTerm('tags', 'meh')->save();
        $this->makeTerm('tags', 'wow')->save();

        $this->makeCollection('test')->taxonomies(['tags'])->save();

        $this->makeEntry('test', '1')->data(['tags' => ['rad', 'wow']])->save();
        $this->makeEntry('test', '2')->data(['tags' => ['rad']])->save();
        $this->makeEntry('test', '3')->data(['tags' => ['meh']])->save();

        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags]=rad', 2);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags]=boring', 0);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:in]=boring', 0);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:in]=boring,rad', 2);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:in]=wow,rad', 2);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:in]=rad,meh', 3);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:not_in]=boring', 3);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:not_in]=rad', 1);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:not_in]=boring,rad', 1);
        $this->assertEndpointDataCount('/api/collections/test/entries?filter[taxonomy:tags:not_in]=rad,meh,wow', 0);
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

    /** @test */
    public function next_prev_link_include_original_query_params()
    {
        Facades\Config::set('statamic.api.resources.collections', true);
        Facades\Config::set('statamic.api.cache', false);

        Facades\Collection::make('pages')->save();

        Facades\Entry::make()->collection('pages')->id('dance')->slug('dance')->published(true)->save();
        Facades\Entry::make()->collection('pages')->id('swing')->slug('swing')->published(true)->save();
        Facades\Entry::make()->collection('pages')->id('jazz')->slug('jazz')->published(true)->save();

        $this
            ->get('/api/collections/pages/entries?limit=2&sort=-date&filter[published]=true&unknown=param')
            ->assertJsonPath('links.first', 'http://localhost/api/collections/pages/entries?filter%5Bpublished%5D=true&limit=2&sort=-date&page=1')
            ->assertJsonPath('links.next', 'http://localhost/api/collections/pages/entries?filter%5Bpublished%5D=true&limit=2&sort=-date&page=2');
    }

    /** @test */
    public function relationships_are_shallow_augmented()
    {
        Facades\Config::set('statamic.api.resources.collections', true);

        // use two collections just so the related entries dont show up in the listing.
        Facades\Collection::make('pages')->save();
        Facades\Collection::make('other')->save();

        $blueprint = Blueprint::makeFromFields([
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entries'],
        ]);
        $other = Blueprint::makeFromFields([]);
        BlueprintRepository::shouldReceive('in')->with('collections/pages')->andReturn(collect([
            'page' => $blueprint->setHandle('post'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('collections/other')->andReturn(collect([
            'other' => $other->setHandle('other'),
        ]));

        Facades\Entry::make()->collection('other')->id('two')->slug('two')->published(true)->save();
        Facades\Entry::make()->collection('other')->id('three')->slug('three')->published(true)->save();

        Facades\Entry::make()->collection('pages')->id('about')->slug('about')->published(true)
            ->set('foo', 'foo text value')
            ->set('bar', ['two', 'three'])
            ->save();

        $this->get('/api/collections/pages/entries')->assertJson([
            'data' => [
                [
                    'foo' => 'foo text value',
                    'bar' => [
                        ['id' => 'two'],
                        ['id' => 'three'],
                    ],
                ],
            ],
        ]);

        $this->get('/api/collections/pages/entries/about')->assertJson([
            'data' => [
                'foo' => 'foo text value',
                'bar' => [
                    ['id' => 'two'],
                    ['id' => 'three'],
                ],
            ],
        ]);
    }

    /**
     * @test
     * @dataProvider userPasswordFilterProvider
     */
    public function it_doesnt_allow_filtering_users_by_password($filter)
    {
        Facades\Config::set('statamic.api.resources.users', true);

        User::make()->id('one')->email('one@domain.com')->passwordHash('abc')->save();
        User::make()->id('two')->email('two@domain.com')->passwordHash('def')->save();

        $this
            ->get("/api/users?filter[{$filter}]=abc")
            ->assertJson([
                'data' => [
                    ['id' => 'one'],
                    ['id' => 'two'], // this one would be filtered out if the password was allowed
                ],
            ]);
    }

    /** @test */
    public function it_replaces_entries_using_live_preview_token()
    {
        Facades\Config::set('statamic.api.resources.collections', true);
        Facades\Collection::make('pages')->save();
        Facades\Entry::make()->collection('pages')->id('dance')->set('title', 'Dance')->slug('dance')->save();

        $substitute = Facades\Entry::make()->collection('pages')->id('dance')->set('title', 'Dance modified in live preview')->slug('dance');

        $this->get('/api/collections/pages/entries/dance')->assertJson([
            'data' => [
                'title' => 'Dance',
            ],
        ]);

        LivePreview::tokenize('test-token', $substitute);

        $this->get('/api/collections/pages/entries/dance?token=test-token')->assertJson([
            'data' => [
                'title' => 'Dance modified in live preview',
            ],
        ]);
    }

    /** @test */
    public function it_replaces_terms_using_live_preview_token()
    {
        Facades\Config::set('statamic.api.resources.taxonomies', true);
        Facades\Taxonomy::make('topics')->save();
        Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data(['title' => 'Dance'])->save();

        $substitute = Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data(['title' => 'Dance modified in live preview']);

        $this->get('/api/taxonomies/topics/terms/dance')->assertJson([
            'data' => [
                'title' => 'Dance',
            ],
        ]);

        LivePreview::tokenize('test-token', $substitute);

        $this->get('/api/taxonomies/topics/terms/dance?token=test-token')->assertJson([
            'data' => [
                'title' => 'Dance modified in live preview',
            ],
        ]);
    }

    public function userPasswordFilterProvider()
    {
        return collect([
            'password',
            'password:is',
            'password:regex',
            'password_hash',
            'password_hash:is',
            'password_hash:regex',
        ])->mapWithKeys(fn ($filter) => [$filter => [$filter]])->all();
    }

    /**
     * @test
     * @dataProvider termNotFoundProvider
     */
    public function it_handles_not_found_terms($url, $requestShouldSucceed)
    {
        Facades\Config::set('statamic.api.resources.taxonomies', true);

        Facades\Taxonomy::make('tags')->save();
        Facades\Taxonomy::make('categories')->save();

        Facades\Term::make('test')->taxonomy('tags')->dataForLocale('en', [])->save();

        if ($requestShouldSucceed) {
            $this->assertEndpointSuccessful($url);
        } else {
            $this->assertEndpointNotFound($url);
        }
    }

    public function termNotFoundProvider()
    {
        return [
            'valid term id' => ['/api/taxonomies/tags/terms/test', true],
            'invalid term id' => ['/api/taxonomies/tags/terms/missing', false],
            'valid term id but wrong collection' => ['/api/taxonomies/categories/terms/test', false],
        ];
    }

    private function makeCollection($handle)
    {
        return Facades\Collection::make($handle);
    }

    private function makeEntry($collection, $slug)
    {
        return Facades\Entry::make()->collection($collection)->id($slug)->slug($slug);
    }

    private function makeTaxonomy($handle)
    {
        return Facades\Taxonomy::make($handle);
    }

    private function makeTerm($taxonomy, $slug, $data = [])
    {
        return Facades\Term::make()->taxonomy($taxonomy)->inDefaultLocale()->slug($slug)->data($data);
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
