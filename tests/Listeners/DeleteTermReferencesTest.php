<?php

namespace Tests\Listeners;

use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteTermReferencesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxonomy = tap(Facades\Taxonomy::make('test'))->save();
        $this->term1 = tap(Facades\Term::make()->taxonomy('test')->inDefaultLocale()->slug('term1')->data([]))->save();
        $this->term2 = tap(Facades\Term::make()->taxonomy('test')->inDefaultLocale()->slug('term2')->data([]))->save();
        $this->term3 = tap(Facades\Term::make()->taxonomy('test')->inDefaultLocale()->slug('term3')->data([]))->save();
    }

    /** @test */
    public function it_updates_single_term_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'delete',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['test'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'leave_be',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['test'],
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'delete' => 'term1',
            'leave_be' => 'term2',
        ]))->save();

        $this->assertEquals('term1', $entry->get('delete'));
        $this->assertEquals('term2', $entry->get('leave_be'));

        $this->term1->delete();

        $this->assertNull($entry->fresh()->get('delete'));
        $this->assertEquals('term2', $entry->fresh()->get('leave_be'));
    }

     /** @test */
     public function it_updates_multi_terms_fields()
     {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'delete_one',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['test'],
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'delete_two',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['test'],
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'delete_all',
                    'field' => [
                        'type' => 'terms',
                        'taxonomies' => ['test'],
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'delete_one' => ['term3', 'term1'],
            'delete_two' => ['term1', 'term2', 'term3'],
            'delete_all' => ['term2', 'term3'],
        ]))->save();

        $this->assertEquals(['term3', 'term1'], $entry->get('delete_one'));
        $this->assertEquals(['term1', 'term2', 'term3'], $entry->get('delete_two'));

        $this->term2->delete();
        $this->term3->delete();

        $this->assertEquals(['term1'], $entry->fresh()->get('delete_one'));
        $this->assertEquals(['term1'], $entry->fresh()->get('delete_two'));
        $this->assertEquals([], $entry->fresh()->get('delete_all'));
    }

    /** @test */
    public function it_updates_scoped_single_term_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'delete',
                    'field' => [
                        'type' => 'terms',
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'leave_be',
                    'field' => [
                        'type' => 'terms',
                        'max_items' => 1,
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'delete' => 'test::term3',
            'leave_be' => 'test::term2',
        ]))->save();

        $this->assertEquals('test::term3', $entry->get('delete'));
        $this->assertEquals('test::term2', $entry->get('leave_be'));

        $this->term3->delete();

        $this->assertNull($entry->fresh()->get('delete'));
        $this->assertEquals('test::term2', $entry->fresh()->get('leave_be'));
    }

   /** @test */
   public function it_updates_scoped_multi_terms_fields()
   {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'delete_one',
                    'field' => [
                        'type' => 'terms',
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'delete_two',
                    'field' => [
                        'type' => 'terms',
                        'mode' => 'select',
                    ],
                ],
                [
                    'handle' => 'delete_all',
                    'field' => [
                        'type' => 'terms',
                        'mode' => 'select',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'delete_one' => ['test::term3', 'test::term1'],
            'delete_two' => ['test::term1', 'test::term2', 'test::term3'],
            'delete_all' => ['test::term2', 'test::term3'],
        ]))->save();

        $this->assertEquals(['test::term3', 'test::term1'], $entry->get('delete_one'));
        $this->assertEquals(['test::term1', 'test::term2', 'test::term3'], $entry->get('delete_two'));
        $this->assertEquals(['test::term2', 'test::term3'], $entry->get('delete_all'));

        $this->term2->delete();
        $this->term3->delete();

        $this->assertEquals(['test::term1'], $entry->fresh()->get('delete_one'));
        $this->assertEquals(['test::term1'], $entry->fresh()->get('delete_two'));
        $this->assertEquals([], $entry->fresh()->get('delete_all'));
    }

    protected function setInBlueprints($namespace, $blueprintContents)
    {
        $taxonomyBlueprint = $this->taxonomy->fallbackTermBlueprint();
        $blueprint = tap(Facades\Blueprint::make()->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('in')->with('taxonomies/test')->andReturn(collect([$taxonomyBlueprint]));
        Facades\Blueprint::shouldReceive('in')->with($namespace)->andReturn(collect([$blueprint]));
    }
}
