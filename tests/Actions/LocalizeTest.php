<?php

namespace Tests\Actions;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\Localize;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LocalizeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => '/fr/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => '/de/'],
        ]);

        Collection::make('test')->sites(['en', 'fr', 'de'])->save();
    }

    #[Test]
    public function it_is_visible_for_multisite_entries_missing_localizations()
    {
        $entry = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();

        $action = (new Localize)->context(['view' => 'list'])->items([$entry]);

        $this->assertTrue($action->visibleTo($entry));
    }

    #[Test]
    public function it_is_hidden_when_all_localizations_exist()
    {
        $en = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        EntryFactory::id('alfa-fr')->collection('test')->slug('alfa')->locale('fr')->origin('alfa')->create();
        EntryFactory::id('alfa-de')->collection('test')->slug('alfa')->locale('de')->origin('alfa')->create();

        $action = (new Localize)->context(['view' => 'list'])->items([$en]);

        $this->assertFalse($action->visibleTo($en));
    }

    #[Test]
    public function it_is_hidden_for_single_site_collections()
    {
        Collection::make('single')->sites(['en'])->save();
        $entry = EntryFactory::id('alfa')->collection('single')->slug('alfa')->locale('en')->create();

        $action = (new Localize)->context(['view' => 'list'])->items([$entry]);

        $this->assertFalse($action->visibleTo($entry));
    }

    #[Test]
    public function it_is_visible_to_bulk_when_any_entry_is_missing_a_localization()
    {
        $alfa = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        $bravo = EntryFactory::id('bravo')->collection('test')->slug('bravo')->locale('en')->create();
        EntryFactory::id('bravo-fr')->collection('test')->slug('bravo')->locale('fr')->origin('bravo')->create();
        EntryFactory::id('bravo-de')->collection('test')->slug('bravo')->locale('de')->origin('bravo')->create();

        $items = collect([$alfa, $bravo]);
        $action = (new Localize)->context(['view' => 'list'])->items($items);

        $this->assertTrue($action->visibleToBulk($items));
    }

    #[Test]
    public function it_is_hidden_from_bulk_when_all_entries_are_fully_localized()
    {
        $alfa = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        EntryFactory::id('alfa-fr')->collection('test')->slug('alfa')->locale('fr')->origin('alfa')->create();
        EntryFactory::id('alfa-de')->collection('test')->slug('alfa')->locale('de')->origin('alfa')->create();

        $items = collect([$alfa]);
        $action = (new Localize)->context(['view' => 'list'])->items($items);

        $this->assertFalse($action->visibleToBulk($items));
    }

    #[Test]
    public function it_is_hidden_from_bulk_when_selection_includes_non_entries()
    {
        $entry = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        $items = collect([$entry, 'not-an-entry']);
        $action = (new Localize)->context(['view' => 'list'])->items($items);

        $this->assertFalse($action->visibleToBulk($items));
    }

    #[Test]
    public function it_is_hidden_from_bulk_when_entries_are_from_different_collections()
    {
        Collection::make('other')->sites(['en', 'fr', 'de'])->save();

        $alfa = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        $bravo = EntryFactory::id('bravo')->collection('other')->slug('bravo')->locale('en')->create();
        $items = collect([$alfa, $bravo]);
        $action = (new Localize)->context(['view' => 'list'])->items($items);

        $this->assertFalse($action->visibleToBulk($items));
    }

    #[Test]
    public function it_is_hidden_from_bulk_when_multisite_is_disabled()
    {
        Config::set('statamic.system.multisite', false);

        $entry = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        $items = collect([$entry]);
        $action = (new Localize)->context(['view' => 'list'])->items($items);

        $this->assertFalse($action->visibleToBulk($items));
    }

    #[Test]
    public function it_is_hidden_from_bulk_for_single_site_collections()
    {
        Collection::make('single')->sites(['en'])->save();
        $entry = EntryFactory::id('alfa')->collection('single')->slug('alfa')->locale('en')->create();
        $items = collect([$entry]);
        $action = (new Localize)->context(['view' => 'list'])->items($items);

        $this->assertFalse($action->visibleToBulk($items));
    }

    #[Test]
    public function it_authorizes_users_who_can_edit_the_entry()
    {
        $this->setTestRoles([
            'editor' => ['edit test entries', 'access en site'],
            'viewer' => ['view test entries', 'access en site'],
        ]);

        $userWithPermission = tap(User::make()->assignRole('editor'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('viewer'))->save();
        $entry = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();

        $this->assertTrue((new Localize)->authorize($userWithPermission, $entry));
        $this->assertFalse((new Localize)->authorize($userWithoutPermission, $entry));
    }

    #[Test]
    public function it_localizes_entries_missing_the_selected_site()
    {
        $this->actingAs(User::make()->makeSuper()->save());

        $alfa = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->data(['title' => 'Alfa'])->create();
        $bravo = EntryFactory::id('bravo')->collection('test')->slug('bravo')->locale('en')->data(['title' => 'Bravo'])->create();
        EntryFactory::id('bravo-fr')->collection('test')->slug('bravo')->locale('fr')->origin('bravo')->create();

        $message = (new Localize)->run(collect([$alfa, $bravo]), ['site' => 'fr']);

        $this->assertTrue($alfa->fresh()->existsIn('fr'));
        $this->assertEquals('bravo-fr', $bravo->fresh()->in('fr')->id());
        $this->assertEquals('alfa', $alfa->fresh()->in('fr')->origin()->id());
        $this->assertStringContainsString('Created 1', $message);
        $this->assertStringContainsString('skipped 1', $message);
    }

    #[Test]
    public function it_only_offers_sites_that_are_missing_for_selected_entries()
    {
        $en = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        EntryFactory::id('alfa-fr')->collection('test')->slug('alfa')->locale('fr')->origin('alfa')->create();

        $action = (new Localize)->context(['view' => 'list'])->items([$en]);
        $siteField = $action->fields()->get('site');

        $this->assertEquals([
            'de' => 'German',
        ], $siteField->get('options'));
    }

    #[Test]
    public function it_rejects_sites_that_are_not_offered()
    {
        $en = EntryFactory::id('alfa')->collection('test')->slug('alfa')->locale('en')->create();
        EntryFactory::id('alfa-fr')->collection('test')->slug('alfa')->locale('fr')->origin('alfa')->create();

        $action = (new Localize)->context(['view' => 'list'])->items([$en]);

        $this->assertFalse(
            $action->fields()->addValues(['site' => 'fr'])->validator()->validator()->passes()
        );

        $this->assertTrue(
            $action->fields()->addValues(['site' => 'de'])->validator()->validator()->passes()
        );
    }
}
