<?php

namespace Tests\Policies;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;

class EntryPolicyTest extends PolicyTestCase
{
    #[Test]
    public function entry_is_viewable_with_view_permissions()
    {
        $user = $this->userWithPermissions(['view alfa entries']);

        $collectionA = tap(Collection::make('alfa'))->save();
        $collectionB = tap(Collection::make('bravo'))->save();
        $entryA = EntryFactory::id('1')->collection($collectionA)->create();
        $entryB = EntryFactory::id('2')->collection($collectionB)->create();

        $this->assertTrue($user->can('view', $entryA));
        $this->assertFalse($user->can('edit', $entryA));
        $this->assertFalse($user->can('view', $entryB));
        $this->assertFalse($user->can('edit', $entryB));
    }

    #[Test]
    public function entry_is_viewable_with_view_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de', 'es']);

        $user = $this->userWithPermissions([
            'view alfa entries',
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
        ]);

        $collection = tap(Collection::make('alfa')->sites(['en', 'fr', 'de']))->save();

        $entryEn = EntryFactory::id('1')->collection($collection)->locale('en')->create();
        $entryFr = EntryFactory::id('2')->collection($collection)->locale('fr')->create();
        $entryDe = EntryFactory::id('3')->collection($collection)->locale('de')->create();

        $this->assertTrue($user->can('view', $entryEn));
        $this->assertFalse($user->can('view', $entryFr));
        $this->assertTrue($user->can('view', $entryDe));
    }

    #[Test]
    public function entry_is_viewable_and_editable_with_edit_permissions()
    {
        $user = $this->userWithPermissions(['edit alfa entries']);

        $collectionA = tap(Collection::make('alfa'))->save();
        $collectionB = tap(Collection::make('bravo'))->save();
        $entryA = EntryFactory::id('1')->collection($collectionA)->create();
        $entryB = EntryFactory::id('2')->collection($collectionB)->create();

        $this->assertTrue($user->can('view', $entryA));
        $this->assertTrue($user->can('edit', $entryA));
        $this->assertFalse($user->can('view', $entryB));
        $this->assertFalse($user->can('edit', $entryB));
    }

    #[Test]
    public function entry_is_editable_with_edit_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de', 'es']);

        $user = $this->userWithPermissions([
            'edit alfa entries',
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
        ]);

        $collection = tap(Collection::make('alfa')->sites(['en', 'fr', 'de']))->save();
        $entryEn = EntryFactory::id('1')->collection($collection)->locale('en')->create();
        $entryFr = EntryFactory::id('2')->collection($collection)->locale('fr')->create();
        $entryDe = EntryFactory::id('3')->collection($collection)->locale('de')->create();

        $this->assertTrue($user->can('view', $entryEn));
        $this->assertTrue($user->can('edit', $entryEn));
        $this->assertFalse($user->can('view', $entryFr));
        $this->assertFalse($user->can('edit', $entryFr));
        $this->assertTrue($user->can('view', $entryDe));
        $this->assertTrue($user->can('edit', $entryDe));
    }

    #[Test]
    public function entry_is_creatable_with_create_permissions()
    {
        $user = $this->userWithPermissions(['create alfa entries']);

        $collectionA = tap(Collection::make('alfa'))->save();
        $collectionB = tap(Collection::make('bravo'))->save();

        $this->assertTrue($user->can('create', [Entry::class, $collectionA]));
        $this->assertFalse($user->can('create', [Entry::class, $collectionB]));
        $this->assertTrue($user->can('store', [Entry::class, $collectionA]));
        $this->assertFalse($user->can('store', [Entry::class, $collectionB]));
    }

    #[Test]
    public function entry_is_creatable_with_create_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de', 'es']);

        $user = $this->userWithPermissions([
            'create alfa entries',
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
            'access es site',
        ]);

        $collection = tap(Collection::make('alfa')->sites(['en', 'fr', 'de']))->save();

        $this->assertTrue($user->can('create', [Entry::class, $collection, Site::get('en')]));
        $this->assertFalse($user->can('create', [Entry::class, $collection, Site::get('fr')]));
        $this->assertTrue($user->can('create', [Entry::class, $collection, Site::get('de')]));
        $this->assertFalse($user->can('create', [Entry::class, $collection, Site::get('es')]));

        // If no site is specified, it should avoid checking site access.
        $this->assertTrue($user->can('create', [Entry::class, $collection]));
    }

    #[Test]
    public function entry_is_not_creatable_without_create_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de', 'es']);

        $user = $this->userWithPermissions([
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
            'access es site',
        ]);

        $collection = tap(Collection::make('alfa')->sites(['en', 'fr', 'es']))->save();

        $this->assertFalse($user->can('create', [Entry::class, $collection, Site::get('en')]));
        $this->assertFalse($user->can('create', [Entry::class, $collection, Site::get('fr')]));
        $this->assertFalse($user->can('create', [Entry::class, $collection, Site::get('de')]));
        $this->assertFalse($user->can('create', [Entry::class, $collection, Site::get('es')]));

        // If no site is specified, it should avoid checking site access.
        $this->assertFalse($user->can('create', [Entry::class, $collection]));
    }

    #[Test]
    public function another_authors_entry_is_editable()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function another_authors_entry_is_editable_with_site_permission()
    {
        $this->markTestIncomplete();
    }
}
