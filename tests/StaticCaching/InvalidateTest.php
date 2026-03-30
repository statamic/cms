<?php

namespace Tests\StaticCaching;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\CollectionTreeEntriesMovedOrRemoved;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\Form;
use Statamic\StaticCaching\Invalidate;
use Statamic\StaticCaching\Invalidator;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class InvalidateTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_invalidates_a_form_when_its_blueprint_is_saved()
    {
        $form = tap(Form::make('contact'))->save();

        $event = new BlueprintSaved($form->blueprint());

        $invalidator = Mockery::mock(Invalidator::class)->shouldReceive('invalidate')->once()->withArgs(function ($form) {
            return $form->handle() === 'contact';
        })->getMock();

        $invalidate = new Invalidate($invalidator);

        $invalidate->invalidateByBlueprint($event);
    }

    #[Test]
    public function it_invalidates_removed_entries_when_collection_tree_is_saving()
    {
        $entry1 = Mockery::mock(Entry::class);
        $entry2 = Mockery::mock(Entry::class);

        EntryFacade::shouldReceive('find')->with('entry-1')->andReturn($entry1);
        EntryFacade::shouldReceive('find')->with('entry-2')->andReturn($entry2);

        $event = new CollectionTreeEntriesMovedOrRemoved(removed: ['entry-1', 'entry-2'], moved: []);

        $invalidator = Mockery::mock(Invalidator::class);
        $invalidator->shouldReceive('invalidate')->with($entry1)->once();
        $invalidator->shouldReceive('invalidate')->with($entry2)->once();

        $invalidate = new Invalidate($invalidator);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }

    #[Test]
    public function it_invalidates_entries_with_changed_ancestry_when_collection_tree_is_saving()
    {
        $entry = Mockery::mock(Entry::class);

        EntryFacade::shouldReceive('find')->with('entry-1')->andReturn($entry);

        $event = new CollectionTreeEntriesMovedOrRemoved(removed: [], moved: ['entry-1']);

        $invalidator = Mockery::mock(Invalidator::class);
        $invalidator->shouldReceive('invalidate')->with($entry)->once();

        $invalidate = new Invalidate($invalidator);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }

    #[Test]
    public function it_does_not_invalidate_entries_only_reordered_within_same_parent_when_collection_tree_is_saving()
    {
        $event = new CollectionTreeEntriesMovedOrRemoved(removed: [], moved: []);

        $invalidator = Mockery::mock(Invalidator::class);
        $invalidator->shouldNotReceive('invalidate');

        $invalidate = new Invalidate($invalidator);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }

    #[Test]
    public function it_skips_entries_that_cannot_be_found_when_collection_tree_is_saving()
    {
        EntryFacade::shouldReceive('find')->with('missing-entry')->andReturn(null);

        $event = new CollectionTreeEntriesMovedOrRemoved(removed: ['missing-entry'], moved: []);

        $invalidator = Mockery::mock(Invalidator::class);
        $invalidator->shouldNotReceive('invalidate');

        $invalidate = new Invalidate($invalidator);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }
}
