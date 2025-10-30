<?php

namespace Tests\Listeners;

use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateTermReferencesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $topics;
    private $termHoff;
    private $termNorris;

    public function setUp(): void
    {
        parent::setUp();

        // TODO: Test localized terms?
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
        ]);

        $this->topics = tap(Facades\Taxonomy::make('topics'))->save();
        $this->termHoff = tap(Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('hoff')->data([]))->save();
        $this->termNorris = tap(Facades\Term::make()->taxonomy('topics')->inDefaultLocale()->slug('norris')->data([]))->save();
    }

    protected function disableUpdateReferences($app)
    {
        $app['config']->set('statamic.system.update_references', false);
    }

    #[Test]
    public function it_updates_single_term_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'non_favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'hoff',
            'non_favourite' => 'norris',
        ]))->save();

        $this->assertEquals('hoff', $entry->get('favourite'));
        $this->assertEquals('norris', $entry->get('non_favourite'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('hoff-new', $entry->fresh()->get('favourite'));
        $this->assertEquals('norris', $entry->fresh()->get('non_favourite'));
    }

    #[Test]
    public function it_updates_multi_terms_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourites',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourites' => ['hoff', 'norris'],
        ]))->save();

        $this->assertEquals(['hoff', 'norris'], $entry->get('favourites'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals(['hoff-new', 'norris'], $entry->fresh()->get('favourites'));
    }

    #[Test]
    public function it_updates_terms_fields_regardless_of_max_items_setting()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'non_favourites',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => ['hoff'], // assuming it was previously `max_items` > 1
            'non_favourites' => 'norris', // assuming it was previously `max_files` == 1
        ]))->save();

        $this->assertEquals(['hoff'], $entry->get('favourite'));
        $this->assertEquals('norris', $entry->get('non_favourites'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals(['hoff-new'], $entry->fresh()->get('favourite'));
        $this->assertEquals('norris', $entry->fresh()->get('non_favourites'));
    }

    #[Test]
    public function it_nullifies_references_when_deleting_a_term()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'non_favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'favourites',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'hoff',
            'non_favourite' => 'norris',
            'favourites' => ['hoff', 'norris'],
        ]))->save();

        $this->assertEquals('hoff', $entry->get('favourite'));
        $this->assertEquals('norris', $entry->get('non_favourite'));
        $this->assertEquals(['hoff', 'norris'], $entry->get('favourites'));

        $this->termHoff->delete();

        $this->assertFalse($entry->fresh()->has('favourite'));
        $this->assertEquals('norris', $entry->fresh()->get('non_favourite'));
        $this->assertEquals(['norris'], $entry->fresh()->get('favourites'));

        $this->termNorris->delete();

        $this->assertFalse($entry->fresh()->has('non_favourite'));
        $this->assertFalse($entry->fresh()->has('favourites'));
    }

    #[Test]
    public function it_updates_scoped_single_term_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'non_favourite',
                    'field' => [
                        'type' => 'terms',
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'topics::hoff',
            'non_favourite' => 'topics::norris',
        ]))->save();

        $this->assertEquals('topics::hoff', $entry->get('favourite'));
        $this->assertEquals('topics::norris', $entry->get('non_favourite'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('topics::hoff-new', $entry->fresh()->get('favourite'));
        $this->assertEquals('topics::norris', $entry->fresh()->get('non_favourite'));
    }

    #[Test]
    public function it_updates_scoped_multi_terms_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourites',
                    'field' => [
                        'type' => 'terms',
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourites' => ['topics::hoff', 'topics::norris'],
        ]))->save();

        $this->assertEquals(['topics::hoff', 'topics::norris'], $entry->get('favourites'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals(['topics::hoff-new', 'topics::norris'], $entry->fresh()->get('favourites'));
    }

    #[Test]
    public function it_updates_scoped_term_fields_regardless_of_max_items_setting()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'non_favourites',
                    'field' => [
                        'type' => 'terms',
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => ['topics::hoff'], // assuming it was previously `max_items` > 1
            'non_favourites' => 'topics::norris', // assuming it was previously `max_files` == 1
        ]))->save();

        $this->assertEquals(['topics::hoff'], $entry->get('favourite'));
        $this->assertEquals('topics::norris', $entry->get('non_favourites'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals(['topics::hoff-new'], $entry->fresh()->get('favourite'));
        $this->assertEquals('topics::norris', $entry->fresh()->get('non_favourites'));
    }

    #[Test]
    public function it_nullifies_references_when_deleting_a_scoped_term()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'non_favourite',
                    'field' => [
                        'type' => 'terms',
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'favourites',
                    'field' => [
                        'type' => 'terms',
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'topics::hoff',
            'non_favourite' => 'topics::norris',
            'favourites' => ['topics::hoff', 'topics::norris'],
        ]))->save();

        $this->assertEquals('topics::hoff', $entry->get('favourite'));
        $this->assertEquals('topics::norris', $entry->get('non_favourite'));
        $this->assertEquals(['topics::hoff', 'topics::norris'], $entry->get('favourites'));

        $this->termHoff->delete();

        $this->assertFalse($entry->fresh()->has('favourite'));
        $this->assertEquals('topics::norris', $entry->fresh()->get('non_favourite'));
        $this->assertEquals(['topics::norris'], $entry->fresh()->get('favourites'));

        $this->termNorris->delete();

        $this->assertFalse($entry->fresh()->has('non_favourite'));
        $this->assertFalse($entry->fresh()->has('favourites'));
    }

    #[Test]
    #[DefineEnvironment('disableUpdateReferences')]
    public function it_can_be_disabled()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'non_favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'favourites',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'hoff',
            'non_favourite' => 'norris',
            'favourites' => ['hoff', 'norris'],
        ]))->save();

        $this->assertEquals('hoff', $entry->get('favourite'));
        $this->assertEquals('norris', $entry->get('non_favourite'));
        $this->assertEquals(['hoff', 'norris'], $entry->get('favourites'));

        $this->termNorris->delete();
        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('hoff', $entry->fresh()->get('favourite'));
        $this->assertEquals('norris', $entry->fresh()->get('non_favourite'));
        $this->assertEquals(['hoff', 'norris'], $entry->fresh()->get('favourites'));
    }

    #[Test]
    public function it_updates_nested_term_fields_within_replicator_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'reppy',
                    'field' => [
                        'type' => 'replicator',
                        'sets' => [
                            'group_one' => [
                                'sets' => [
                                    'set_one' => [
                                        'fields' => [
                                            [
                                                'handle' => 'favourite',
                                                'field' => [
                                                    'type' => 'terms',
                                                    'taxonomies' => ['topics'],
                                                    'max_items' => 1,
                                                ],
                                            ],
                                            [
                                                'handle' => 'favourites',
                                                'field' => [
                                                    'type' => 'terms',
                                                    'taxonomies' => ['topics'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'set_two' => [
                                        'fields' => [
                                            [
                                                'handle' => 'not_term',
                                                'field' => [
                                                    'type' => 'text',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'reppy' => [
                [
                    'type' => 'set_one',
                    'favourite' => 'norris',
                    'favourites' => ['hoff', 'norris'],
                ],
                [
                    'type' => 'set_two',
                    'not_term' => 'not a term',
                ],
                [
                    'type' => 'set_one',
                    'favourite' => 'hoff',
                    'favourites' => ['hoff', 'norris', 'lee'],
                ],
            ],
        ]))->save();

        $this->assertEquals('norris', Arr::get($entry->data(), 'reppy.0.favourite'));
        $this->assertEquals(['hoff', 'norris'], Arr::get($entry->data(), 'reppy.0.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->data(), 'reppy.1.not_term'));
        $this->assertEquals('hoff', Arr::get($entry->data(), 'reppy.2.favourite'));
        $this->assertEquals(['hoff', 'norris', 'lee'], Arr::get($entry->data(), 'reppy.2.favourites'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->delete();

        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'reppy.0.favourite'));
        $this->assertEquals(['norris-new'], Arr::get($entry->fresh()->data(), 'reppy.0.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->fresh()->data(), 'reppy.1.not_term'));
        $this->assertFalse(Arr::has($entry->fresh()->data(), 'reppy.2.favourite'));
        $this->assertEquals(['norris-new', 'lee'], Arr::get($entry->fresh()->data(), 'reppy.2.favourites'));
    }

    #[Test]
    public function it_updates_nested_term_fields_within_legacy_replicator_configs()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'reppy',
                    'field' => [
                        'type' => 'replicator',
                        'sets' => [
                            'set_one' => [
                                'fields' => [
                                    [
                                        'handle' => 'favourite',
                                        'field' => [
                                            'type' => 'terms',
                                            'taxonomies' => ['topics'],
                                            'max_items' => 1,
                                        ],
                                    ],
                                    [
                                        'handle' => 'favourites',
                                        'field' => [
                                            'type' => 'terms',
                                            'taxonomies' => ['topics'],
                                        ],
                                    ],
                                ],
                            ],
                            'set_two' => [
                                'fields' => [
                                    [
                                        'handle' => 'not_term',
                                        'field' => [
                                            'type' => 'text',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'reppy' => [
                [
                    'type' => 'set_one',
                    'favourite' => 'norris',
                    'favourites' => ['hoff', 'norris'],
                ],
                [
                    'type' => 'set_two',
                    'not_term' => 'not a term',
                ],
                [
                    'type' => 'set_one',
                    'favourite' => 'hoff',
                    'favourites' => ['hoff', 'norris', 'lee'],
                ],
            ],
        ]))->save();

        $this->assertEquals('norris', Arr::get($entry->data(), 'reppy.0.favourite'));
        $this->assertEquals(['hoff', 'norris'], Arr::get($entry->data(), 'reppy.0.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->data(), 'reppy.1.not_term'));
        $this->assertEquals('hoff', Arr::get($entry->data(), 'reppy.2.favourite'));
        $this->assertEquals(['hoff', 'norris', 'lee'], Arr::get($entry->data(), 'reppy.2.favourites'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->delete();

        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'reppy.0.favourite'));
        $this->assertEquals(['norris-new'], Arr::get($entry->fresh()->data(), 'reppy.0.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->fresh()->data(), 'reppy.1.not_term'));
        $this->assertFalse(Arr::has($entry->fresh()->data(), 'reppy.2.favourite'));
        $this->assertEquals(['norris-new', 'lee'], Arr::get($entry->fresh()->data(), 'reppy.2.favourites'));
    }

    #[Test]
    public function it_updates_nested_term_fields_within_grid_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'griddy',
                    'field' => [
                        'type' => 'grid',
                        'fields' => [
                            [
                                'handle' => 'favourite',
                                'field' => [
                                    'type' => 'terms',
                                    'taxonomies' => ['topics'],
                                    'max_items' => 1,
                                ],
                            ],
                            [
                                'handle' => 'favourites',
                                'field' => [
                                    'type' => 'terms',
                                    'taxonomies' => ['topics'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'griddy' => [
                [
                    'favourite' => 'norris',
                    'favourites' => ['hoff', 'norris'],
                ],
                [
                    'favourite' => 'hoff',
                    'favourites' => ['hoff', 'norris', 'lee'],
                ],
            ],
        ]))->save();

        $this->assertEquals('norris', Arr::get($entry->data(), 'griddy.0.favourite'));
        $this->assertEquals(['hoff', 'norris'], Arr::get($entry->data(), 'griddy.0.favourites'));
        $this->assertEquals('hoff', Arr::get($entry->data(), 'griddy.1.favourite'));
        $this->assertEquals(['hoff', 'norris', 'lee'], Arr::get($entry->data(), 'griddy.1.favourites'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->delete();

        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'griddy.0.favourite'));
        $this->assertEquals(['norris-new'], Arr::get($entry->fresh()->data(), 'griddy.0.favourites'));
        $this->assertFalse(Arr::has($entry->fresh()->data(), 'griddy.1.favourite'));
        $this->assertEquals(['norris-new', 'lee'], Arr::get($entry->fresh()->data(), 'griddy.1.favourites'));
    }

    #[Test]
    public function it_updates_nested_term_fields_within_bard_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'bardo',
                    'field' => [
                        'type' => 'bard',
                        'sets' => [
                            'group_one' => [
                                'sets' => [
                                    'set_one' => [
                                        'fields' => [
                                            [
                                                'handle' => 'favourite',
                                                'field' => [
                                                    'type' => 'terms',
                                                    'taxonomies' => ['topics'],
                                                    'max_items' => 1,
                                                ],
                                            ],
                                            [
                                                'handle' => 'favourites',
                                                'field' => [
                                                    'type' => 'terms',
                                                    'taxonomies' => ['topics'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'set_two' => [
                                        'fields' => [
                                            [
                                                'handle' => 'not_term',
                                                'field' => [
                                                    'type' => 'text',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'bardo' => [
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'set_one',
                            'favourite' => 'norris',
                            'favourites' => ['hoff', 'norris'],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'not_term' => 'not a term',
                ],
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'set_one',
                            'favourite' => 'hoff',
                            'favourites' => ['hoff', 'norris', 'lee'],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $this->assertEquals('norris', Arr::get($entry->data(), 'bardo.0.attrs.values.favourite'));
        $this->assertEquals(['hoff', 'norris'], Arr::get($entry->data(), 'bardo.0.attrs.values.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->data(), 'bardo.1.not_term'));
        $this->assertEquals('hoff', Arr::get($entry->data(), 'bardo.2.attrs.values.favourite'));
        $this->assertEquals(['hoff', 'norris', 'lee'], Arr::get($entry->data(), 'bardo.2.attrs.values.favourites'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->delete();

        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'bardo.0.attrs.values.favourite'));
        $this->assertEquals(['norris-new'], Arr::get($entry->fresh()->data(), 'bardo.0.attrs.values.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->fresh()->data(), 'bardo.1.not_term'));
        $this->assertFalse(Arr::has($entry->fresh()->data(), 'bardo.2.attrs.values.favourite'));
        $this->assertEquals(['norris-new', 'lee'], Arr::get($entry->fresh()->data(), 'bardo.2.attrs.values.favourites'));
    }

    #[Test]
    public function it_updates_nested_term_fields_within_legacy_bard_config()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'bardo',
                    'field' => [
                        'type' => 'bard',
                        'sets' => [
                            'set_one' => [
                                'fields' => [
                                    [
                                        'handle' => 'favourite',
                                        'field' => [
                                            'type' => 'terms',
                                            'taxonomies' => ['topics'],
                                            'max_items' => 1,
                                        ],
                                    ],
                                    [
                                        'handle' => 'favourites',
                                        'field' => [
                                            'type' => 'terms',
                                            'taxonomies' => ['topics'],
                                        ],
                                    ],
                                ],
                            ],
                            'set_two' => [
                                'fields' => [
                                    [
                                        'handle' => 'not_term',
                                        'field' => [
                                            'type' => 'text',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'bardo' => [
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'set_one',
                            'favourite' => 'norris',
                            'favourites' => ['hoff', 'norris'],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'not_term' => 'not a term',
                ],
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'set_one',
                            'favourite' => 'hoff',
                            'favourites' => ['hoff', 'norris', 'lee'],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $this->assertEquals('norris', Arr::get($entry->data(), 'bardo.0.attrs.values.favourite'));
        $this->assertEquals(['hoff', 'norris'], Arr::get($entry->data(), 'bardo.0.attrs.values.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->data(), 'bardo.1.not_term'));
        $this->assertEquals('hoff', Arr::get($entry->data(), 'bardo.2.attrs.values.favourite'));
        $this->assertEquals(['hoff', 'norris', 'lee'], Arr::get($entry->data(), 'bardo.2.attrs.values.favourites'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->delete();

        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'bardo.0.attrs.values.favourite'));
        $this->assertEquals(['norris-new'], Arr::get($entry->fresh()->data(), 'bardo.0.attrs.values.favourites'));
        $this->assertEquals('not a term', Arr::get($entry->fresh()->data(), 'bardo.1.not_term'));
        $this->assertFalse(Arr::has($entry->fresh()->data(), 'bardo.2.attrs.values.favourite'));
        $this->assertEquals(['norris-new', 'lee'], Arr::get($entry->fresh()->data(), 'bardo.2.attrs.values.favourites'));
    }

    #[Test]
    public function it_recursively_updates_nested_term_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                    ],
                ],
                [
                    'handle' => 'reppy',
                    'field' => [
                        'type' => 'replicator',
                        'sets' => [
                            'set_one' => [
                                'fields' => [
                                    [
                                        'handle' => 'bard_within_reppy',
                                        'field' => [
                                            'type' => 'bard',
                                            'sets' => [
                                                'set_two' => [
                                                    'fields' => [
                                                        [
                                                            'handle' => 'favourite',
                                                            'field' => [
                                                                'type' => 'terms',
                                                                'taxonomies' => ['topics'],
                                                                'max_items' => 1,
                                                            ],
                                                        ],
                                                        [
                                                            'handle' => 'favourites',
                                                            'field' => [
                                                                'type' => 'terms',
                                                                'taxonomies' => ['topics'],
                                                            ],
                                                        ],
                                                        [
                                                            'handle' => 'griddy',
                                                            'field' => [
                                                                'type' => 'grid',
                                                                'fields' => [
                                                                    [
                                                                        'handle' => 'favourite',
                                                                        'field' => [
                                                                            'type' => 'terms',
                                                                            'taxonomies' => ['topics'],
                                                                            'max_items' => 1,
                                                                        ],
                                                                    ],
                                                                    [
                                                                        'handle' => 'favourites',
                                                                        'field' => [
                                                                            'type' => 'terms',
                                                                            'taxonomies' => ['topics'],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'norris',
            'reppy' => [
                [
                    'type' => 'huh',
                    'not_term' => 'not a term',
                ],
                [
                    'type' => 'set_one',
                    'bard_within_reppy' => [
                        [
                            'type' => 'set',
                            'attrs' => [
                                'values' => [
                                    'type' => 'set_two',
                                    'favourite' => 'norris',
                                    'favourites' => ['hoff', 'norris'],
                                    'griddy' => [
                                        [
                                            'favourite' => 'norris',
                                            'favourites' => ['hoff', 'norris', 'lee'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $this->assertEquals('norris', Arr::get($entry->data(), 'favourite'));
        $this->assertEquals('not a term', Arr::get($entry->data(), 'reppy.0.not_term'));
        $this->assertEquals('norris', Arr::get($entry->data(), 'reppy.1.bard_within_reppy.0.attrs.values.favourite'));
        $this->assertEquals(['hoff', 'norris'], Arr::get($entry->data(), 'reppy.1.bard_within_reppy.0.attrs.values.favourites'));
        $this->assertEquals('norris', Arr::get($entry->data(), 'reppy.1.bard_within_reppy.0.attrs.values.griddy.0.favourite'));
        $this->assertEquals(['hoff', 'norris', 'lee'], Arr::get($entry->data(), 'reppy.1.bard_within_reppy.0.attrs.values.griddy.0.favourites'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->delete();

        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'favourite'));
        $this->assertEquals('not a term', Arr::get($entry->fresh()->data(), 'reppy.0.not_term'));
        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'reppy.1.bard_within_reppy.0.attrs.values.favourite'));
        $this->assertEquals(['norris-new'], Arr::get($entry->fresh()->data(), 'reppy.1.bard_within_reppy.0.attrs.values.favourites'));
        $this->assertEquals('norris-new', Arr::get($entry->fresh()->data(), 'reppy.1.bard_within_reppy.0.attrs.values.griddy.0.favourite'));
        $this->assertEquals(['norris-new', 'lee'], Arr::get($entry->fresh()->data(), 'reppy.1.bard_within_reppy.0.attrs.values.griddy.0.favourites'));
    }

    #[Test]
    public function it_doesnt_update_terms_from_another_taxonomy()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                    ],
                ],
                [
                    'handle' => 'wrong_favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['wrong_topics'],
                        'max_items' => 1,
                    ],
                ],
                [
                    'handle' => 'favourites',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                    ],
                ],
                [
                    'handle' => 'wrong_favourites',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['wrong_topics'],
                    ],
                ],
                [
                    'handle' => 'mixed_terms',
                    'field' => [
                        'type' => 'terms',
                    ],
                ],
                [
                    'handle' => 'wrong_mixed_terms',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['wrong_topics', 'another_wrong_topics'],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'hoff',
            'wrong_favourite' => 'hoff',
            'favourites' => ['hoff', 'norris'],
            'wrong_favourites' => ['hoff', 'norris'],
            'mixed_terms' => ['topics::hoff', 'wrong_topics::hoff'],
            'wrong_mixed_terms' => ['topics::hoff', 'wrong_topics::hoff'],
        ]))->save();

        $this->assertEquals('hoff', $entry->get('favourite'));
        $this->assertEquals('hoff', $entry->get('wrong_favourite'));
        $this->assertEquals(['hoff', 'norris'], $entry->get('favourites'));
        $this->assertEquals(['hoff', 'norris'], $entry->get('wrong_favourites'));
        $this->assertEquals(['topics::hoff', 'wrong_topics::hoff'], $entry->get('mixed_terms'));
        $this->assertEquals(['topics::hoff', 'wrong_topics::hoff'], $entry->get('wrong_mixed_terms'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('hoff-new', $entry->fresh()->get('favourite'));
        $this->assertEquals('hoff', $entry->fresh()->get('wrong_favourite'));
        $this->assertEquals(['hoff-new', 'norris'], $entry->fresh()->get('favourites'));
        $this->assertEquals(['hoff', 'norris'], $entry->fresh()->get('wrong_favourites'));
        $this->assertEquals(['topics::hoff-new', 'wrong_topics::hoff'], $entry->fresh()->get('mixed_terms'));
        $this->assertEquals(['topics::hoff', 'wrong_topics::hoff'], $entry->fresh()->get('wrong_mixed_terms'));
    }

    #[Test]
    public function it_updates_entries()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'hoff',
        ]))->save();

        $this->assertEquals('hoff', $entry->get('favourite'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('hoff-new', $entry->fresh()->get('favourite'));
    }

    #[Test]
    public function it_updates_terms_on_terms()
    {
        $taxonomy = tap(Facades\Taxonomy::make('tags')->sites(['en', 'fr']))->save();

        $this->setInBlueprints('taxonomies/tags', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                    ],
                ],
            ],
        ]);

        $term = Facades\Term::make('test')->taxonomy($taxonomy);

        $term->in('en')->data([
            'favourite' => 'norris',
        ]);

        $term->in('fr')->data([
            'favourite' => 'hoff',
        ]);

        $term->save();

        $this->assertEquals('norris', $term->in('en')->get('favourite'));
        $this->assertEquals('hoff', $term->in('fr')->get('favourite'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('norris-new', $term->in('en')->fresh()->get('favourite'));
        $this->assertEquals('hoff-new', $term->in('fr')->fresh()->get('favourite'));
    }

    #[Test]
    public function it_updates_global_sets()
    {
        $set = Facades\GlobalSet::make('default')->sites(['en', 'fr'])->save();

        $set->in('en')->data(['favourite' => 'norris'])->save();
        $set->in('fr')->data(['favourite' => 'hoff'])->save();

        $this->setSingleBlueprint('globals.default', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                    ],
                ],
            ],
        ]);

        $this->assertEquals('norris', $set->in('en')->get('favourite'));
        $this->assertEquals('hoff', $set->in('fr')->get('favourite'));

        $this->termNorris->slug('norris-new')->save();
        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('norris-new', $set->in('en')->fresh()->get('favourite'));
        $this->assertEquals('hoff-new', $set->in('fr')->fresh()->get('favourite'));
    }

    #[Test]
    public function it_updates_users()
    {
        $user = tap(Facades\User::make()->email('hoff@example.com')->data(['favourite' => 'hoff']))->save();

        $this->setSingleBlueprint('user', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                    ],
                ],
            ],
        ]);

        $this->assertEquals('hoff', $user->get('favourite'));

        $this->termHoff->slug('hoff-new')->save();

        $this->assertEquals('hoff-new', $user->fresh()->get('favourite'));
    }

    #[Test]
    public function it_only_saves_items_when_there_is_something_to_update()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'favourite',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                        'max_items' => 1,
                    ],
                ],
                [
                    'handle' => 'favourites',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['topics'],
                    ],
                ],
            ],
        ]);

        $entryOne = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'hoff',
        ]))->save();

        $entryTwo = tap(Facades\Entry::make()->collection($collection)->data([
            'favourite' => 'unrelated',
            'favourites' => ['unrelated'],
        ]))->save();

        $entryThree = tap(Facades\Entry::make()->collection($collection)->data([]))->save();

        Facades\Entry::shouldReceive('save')->withArgs(function ($arg) use ($entryOne) {
            return $arg->id() === $entryOne->id();
        })->once();

        Facades\Entry::shouldReceive('save')->withArgs(function ($arg) use ($entryTwo) {
            return $arg->id() === $entryTwo->id();
        })->never();

        Facades\Entry::shouldReceive('save')->withArgs(function ($arg) use ($entryThree) {
            return $arg->id() === $entryThree->id();
        })->never();

        Facades\Entry::makePartial();

        $this->termHoff->slug('hoff-new')->save();
    }

    protected function setSingleBlueprint($namespace, $blueprintContents)
    {
        $topicsBlueprint = $this->topics->fallbackTermBlueprint();
        $blueprint = tap(Facades\Blueprint::make('single-blueprint')->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('in')->with('taxonomies/topics')->andReturn(collect([$topicsBlueprint]));
        Facades\Blueprint::shouldReceive('find')->with($namespace)->andReturn($blueprint);
    }

    protected function setInBlueprints($namespace, $blueprintContents)
    {
        $topicsBlueprint = $this->topics->fallbackTermBlueprint();
        $blueprint = tap(Facades\Blueprint::make('set-in-blueprints')->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('in')->with('taxonomies/topics')->andReturn(collect([$topicsBlueprint]));
        Facades\Blueprint::shouldReceive('in')->with($namespace)->andReturn(collect([$blueprint]));
    }
}
