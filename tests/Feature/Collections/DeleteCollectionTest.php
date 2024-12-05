<?php

namespace Tests\Feature\Collections;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Structure;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test')->save();
        $this->assertCount(1, Collection::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertCount(1, Collection::all());
    }

    #[Test]
    public function it_deletes_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test')->save();
        $this->assertCount(1, Collection::all());

        $this
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertOk();

        $this->assertCount(0, Collection::all());
    }

    #[Test]
    public function it_deletes_the_collection_with_localized_entries()
    {
        $this->withoutExceptionHandling();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test')->sites(['en', 'fr'])->save();
        $this->assertCount(1, Collection::all());

        $entry = tap(Entry::make()->locale('en')->slug('test')->collection('test')->data(['title' => 'Test']))->save();
        $entry->makeLocalization('fr')->slug('test-fr')->data(['title' => 'Test FR'])->save();

        $this
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertOk();

        $this->assertCount(0, Collection::all());
    }

    #[Test]
    public function it_deletes_tree_files()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = tap(Collection::make('test')->structureContents(['root' => true]))->save();

        Entry::make()->id('a')->slug('a')->collection('test')->data(['title' => 'A'])->save();
        Entry::make()->id('b')->slug('b')->collection('test')->data(['title' => 'B'])->save();
        Entry::make()->id('c')->slug('c')->collection('test')->data(['title' => 'C'])->save();

        $collection->structure()->in('en')->tree([['entry' => 'a'], ['entry' => 'b'], ['entry' => 'c']]);

        $this
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertOk();

        $this->assertCount(0, Collection::all());
        $this->assertNull(Structure::find('collection::test'));
    }

    #[Test]
    public function it_deletes_tree_files_in_a_multisite()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = tap(Collection::make('test')->sites(['en', 'fr'])->structureContents(['root' => true]))->save();

        Entry::make()->id('a')->slug('a')->locale('en')->collection('test')->data(['title' => 'A'])->save();
        Entry::make()->id('b')->slug('b')->locale('en')->collection('test')->data(['title' => 'B'])->save();
        Entry::make()->id('c')->slug('c')->locale('fr')->collection('test')->data(['title' => 'C'])->save();

        $collection->structure()->in('en')->tree([['entry' => 'a'], ['entry' => 'b']]);
        $collection->structure()->in('fr')->tree([['entry' => 'c']]);

        $this
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertOk();

        $this->assertCount(0, Collection::all());
        $this->assertNull(Structure::find('collection::test'));
    }
}
