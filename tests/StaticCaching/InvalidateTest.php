<?php

namespace Tests\StaticCaching;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;
use Statamic\Events\BlueprintSaved;
use Statamic\Events\CollectionTreeEntriesMovedOrRemoved;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\Form;
use Statamic\StaticCaching\Cacher;
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

        $invalidate = new Invalidate($invalidator, Mockery::mock(Cacher::class));

        $invalidate->invalidateByBlueprint($event);
    }

    private function mockEntry(string $url): Entry
    {
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('descendants')->andReturn(collect());
        $entry->shouldReceive('isRedirect')->andReturn(false);
        $entry->shouldReceive('absoluteUrl')->andReturn($url);

        return $entry;
    }

    #[Test]
    public function it_invalidates_removed_entries_when_collection_tree_is_saving()
    {
        EntryFacade::shouldReceive('find')->with('entry-1')->andReturn($this->mockEntry('http://example.com/entry-1'));
        EntryFacade::shouldReceive('find')->with('entry-2')->andReturn($this->mockEntry('http://example.com/entry-2'));

        $event = new CollectionTreeEntriesMovedOrRemoved(removed: ['entry-1', 'entry-2'], moved: []);

        $cacher = Mockery::mock(Cacher::class);
        $cacher->shouldReceive('invalidateUrls')
            ->with(['http://example.com/entry-1', 'http://example.com/entry-2'])
            ->once();

        $invalidate = new Invalidate(Mockery::mock(Invalidator::class), $cacher);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }

    #[Test]
    public function it_invalidates_entries_with_changed_ancestry_when_collection_tree_is_saving()
    {
        EntryFacade::shouldReceive('find')->with('entry-1')->andReturn($this->mockEntry('http://example.com/entry-1'));

        $event = new CollectionTreeEntriesMovedOrRemoved(removed: [], moved: ['entry-1']);

        $cacher = Mockery::mock(Cacher::class);
        $cacher->shouldReceive('invalidateUrls')
            ->with(['http://example.com/entry-1'])
            ->once();

        $invalidate = new Invalidate(Mockery::mock(Invalidator::class), $cacher);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }

    #[Test]
    public function it_does_not_invalidate_entries_only_reordered_within_same_parent_when_collection_tree_is_saving()
    {
        $event = new CollectionTreeEntriesMovedOrRemoved(removed: [], moved: []);

        $cacher = Mockery::mock(Cacher::class);
        $cacher->shouldNotReceive('invalidateUrls');

        $invalidate = new Invalidate(Mockery::mock(Invalidator::class), $cacher);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }

    #[Test]
    public function it_skips_entries_that_cannot_be_found_when_collection_tree_is_saving()
    {
        EntryFacade::shouldReceive('find')->with('missing-entry')->andReturn(null);

        $event = new CollectionTreeEntriesMovedOrRemoved(removed: ['missing-entry'], moved: []);

        $cacher = Mockery::mock(Cacher::class);
        $cacher->shouldNotReceive('invalidateUrls');

        $invalidate = new Invalidate(Mockery::mock(Invalidator::class), $cacher);

        $invalidate->invalidateMovedOrRemovedEntries($event);
    }
}
