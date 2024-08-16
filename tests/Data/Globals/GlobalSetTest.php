<?php

namespace Tests\Data\Globals;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\GlobalSetCreated;
use Statamic\Events\GlobalSetCreating;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetDeleting;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalSetSaving;
use Statamic\Facades\GlobalSet as GlobalSetFacade;
use Statamic\Facades\GlobalVariables;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Globals\GlobalSet;
use Statamic\Globals\VariablesCollection;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GlobalSetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_file_contents_for_saving_with_a_single_site()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
        ]);

        $set = (new GlobalSet)->title('The title');

        $variables = $set->makeLocalization('en')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
        ]);

        $set->addLocalization($variables);

        $expected = <<<'EOT'
title: 'The title'
data:
  array:
    - 'first one'
    - 'second one'
  string: 'The string'

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }

    #[Test]
    public function it_gets_file_contents_for_saving_with_multiple_sites()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = (new GlobalSet)->title('The title');

        // We set the data but it's basically irrelevant since it won't get saved to this file.
        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });
        $set->in('fr', function ($loc) {
            $loc->data([
                'array' => ['le first one', 'le second one'],
                'string' => 'Le string',
            ]);
        });

        $expected = <<<'EOT'
title: 'The title'

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }

    #[Test]
    public function it_saves_through_the_api()
    {
        Event::fake();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        $set->save();

        Event::assertDispatched(GlobalSetCreating::class, function ($event) use ($set) {
            return $event->globals === $set;
        });

        Event::assertDispatched(GlobalSetSaving::class, function ($event) use ($set) {
            return $event->globals === $set;
        });

        Event::assertDispatched(GlobalSetCreated::class, function ($event) use ($set) {
            return $event->globals === $set;
        });

        Event::assertDispatched(GlobalSetSaved::class, function ($event) use ($set) {
            return $event->globals === $set;
        });
    }

    #[Test]
    public function saving_a_new_global_set_will_create_its_localizations()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        // when it queries for fresh localizations
        GlobalVariables::shouldReceive('whereSet')->with('test')->andReturn(VariablesCollection::make());
        // when it checks if it's new
        GlobalVariables::shouldReceive('find')->with('test::en');
        GlobalVariables::shouldReceive('find')->with('test::fr');
        // when it saves
        GlobalVariables::shouldReceive('save')
            ->withArgs(fn ($arg) => $arg->locale() === 'en')
            ->once();
        GlobalVariables::shouldReceive('save')
            ->withArgs(fn ($arg) => $arg->locale() === 'fr')
            ->once();

        $set = GlobalSet::make('test');
        $set->addLocalization($en = $set->makeLocalization('en')->data(['foo' => 'bar']));
        $set->addLocalization($fr = $set->makeLocalization('fr')->data(['foo' => 'le bar']));
        $set->save();
    }

    #[Test]
    public function saving_an_existing_global_set_will_save_or_delete_its_localizations()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = GlobalSet::make('test');
        $set->addLocalization($en = $set->makeLocalization('en')->data(['foo' => 'bar']));
        $set->addLocalization($fr = $set->makeLocalization('fr')->data(['foo' => 'le bar']));
        $set->addLocalization($de = $set->makeLocalization('de')->data(['foo' => 'der bar']));
        $set->save();

        // when it queries for fresh localizations
        GlobalVariables::shouldReceive('whereSet')->with('test')->andReturn(VariablesCollection::make([
            'en' => $en,
            'fr' => $fr,
            'de' => $de,
        ]));
        // when it checks if it's new
        GlobalVariables::shouldReceive('find')->with('test::en');
        GlobalVariables::shouldReceive('find')->with('test::de');
        // when it saves
        GlobalVariables::shouldReceive('save')
            ->withArgs(fn ($arg) => $arg->locale() === 'en')
            ->once();
        GlobalVariables::shouldReceive('save')
            ->withArgs(fn ($arg) => $arg->locale() === 'de')
            ->once();
        // when it deletes
        GlobalVariables::shouldReceive('delete')
            ->withArgs(fn ($arg) => $arg->locale() === 'fr')
            ->once();

        $set->removeLocalization($fr);
        $set->save();
    }

    #[Test]
    public function it_dispatches_global_set_created_only_once()
    {
        Event::fake();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        GlobalSetFacade::shouldReceive('save')->with($set);
        GlobalSetFacade::shouldReceive('find')->with($set->handle())->times(3)->andReturn(null, $set, $set);

        $set->save();
        $set->save();
        $set->save();

        Event::assertDispatched(GlobalSetSaved::class, 3);
        Event::assertDispatched(GlobalSetCreated::class, 1);
    }

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        $set->saveQuietly();

        Event::assertNotDispatched(GlobalSetCreating::class);
        Event::assertNotDispatched(GlobalSetSaving::class);
        Event::assertNotDispatched(GlobalSetSaved::class);
        Event::assertNotDispatched(GlobalSetCreated::class);
    }

    #[Test]
    public function if_creating_event_returns_false_the_global_set_doesnt_save()
    {
        Event::fake([GlobalSetCreated::class]);

        Event::listen(GlobalSetCreating::class, function () {
            return false;
        });

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        $return = $set->save();

        $this->assertFalse($return);
        Event::assertNotDispatched(GlobalSetCreated::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_global_set_doesnt_save()
    {
        Event::fake([GlobalSetSaved::class]);

        Event::listen(GlobalSetSaving::class, function () {
            return false;
        });

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        $set->save();

        Event::assertNotDispatched(GlobalSetSaved::class);
    }

    #[Test]
    public function it_updates_the_origin_of_descendants_when_saving_an_entry_with_localizations()
    {
        // The issue this test is covering doesn't happen when using the
        // array cache driver, since the objects are stored in memory.
        config(['cache.default' => 'file']);
        Cache::clear();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => '/fr/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => '/de/'],
        ]);

        $global = tap(GlobalSet::make('test'), function ($global) {
            $global->addLocalization($global->makeLocalization('en')->data(['foo' => 'root']));
            $global->addLocalization($global->makeLocalization('fr')->origin('en'));
            $global->addLocalization($global->makeLocalization('de')->origin('fr'));
        })->save();

        $this->assertEquals('root', $global->in('en')->foo);
        $this->assertEquals('root', $global->in('fr')->foo);
        $this->assertEquals('root', $global->in('de')->foo);

        $global = GlobalSet::find('test');
        $global->in('en')->data(['foo' => 'root updated'])->save();

        $this->assertEquals('root updated', $global->in('en')->foo);
        $this->assertEquals('root updated', $global->in('fr')->foo);
        $this->assertEquals('root updated', $global->in('de')->foo);

        $global = GlobalSet::find('test');
        $global->in('fr')->data(['foo' => 'fr updated'])->save();

        $this->assertEquals('root updated', $global->in('en')->foo);
        $this->assertEquals('fr updated', $global->in('fr')->foo);
        $this->assertEquals('fr updated', $global->in('de')->foo);
    }

    #[Test]
    public function it_gets_available_sites_from_localizations()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set = GlobalSet::make('test');
        $set->addLocalization($set->makeLocalization('en'));
        $set->addLocalization($set->makeLocalization('fr'));
        $set->save();

        $this->assertEquals(\Illuminate\Support\Collection::class, get_class($set->sites()));
        $this->assertEquals(['en', 'fr'], $set->sites()->all());
    }

    #[Test]
    public function it_cannot_view_global_sets_from_sites_that_the_user_is_not_authorized_to_see()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $set1 = GlobalSet::make('has_some_french');
        $set1->addLocalization($set1->makeLocalization('en'));
        $set1->addLocalization($set1->makeLocalization('fr'));
        $set1->addLocalization($set1->makeLocalization('de'));
        $set1->save();

        $set2 = GlobalSet::make('has_no_french');
        $set2->addLocalization($set2->makeLocalization('en'));
        $set2->addLocalization($set2->makeLocalization('de'));
        $set2->save();

        $set3 = GlobalSet::make('has_only_french');
        $set3->addLocalization($set3->makeLocalization('fr'));
        $set3->save();

        $this->setTestRoles(['test' => [
            'access cp',
            'edit has_some_french globals',
            'edit has_no_french globals',
            'edit has_only_french globals',
            'access en site',
            // 'access fr site', // Give them access to all data, but not all sites
            'access de site',
        ]]);

        $user = tap(User::make()->assignRole('test'))->save();

        $this->assertTrue($user->can('view', $set1));
        $this->assertTrue($user->can('view', $set2));
        $this->assertFalse($user->can('view', $set3));
    }

    #[Test]
    public function it_fires_a_deleting_event()
    {
        Event::fake();

        $set = (new GlobalSet)->title('SEO Settings');

        $set->delete();

        Event::assertDispatched(GlobalSetDeleting::class, function ($event) use ($set) {
            return $event->globals === $set;
        });
    }

    #[Test]
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        GlobalSet::spy();
        Event::fake([GlobalSetDeleted::class]);

        Event::listen(GlobalSetDeleting::class, function () {
            return false;
        });

        $set = (new GlobalSet)->title('SEO Settings');

        $return = $set->delete();

        $this->assertFalse($return);
        GlobalSet::shouldNotHaveReceived('delete');
        Event::assertNotDispatched(GlobalSetDeleted::class);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        $set = (new GlobalSet)->title('SEO Settings');

        $return = $set->deleteQuietly();

        Event::assertNotDispatched(GlobalSetDeleting::class);
        Event::assertNotDispatched(GlobalSetDeleted::class);

        $this->assertTrue($return);
    }
}
