<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_shows_the_entry_form()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_overrides_values_from_the_working_copy()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_marks_as_read_only_if_you_only_have_view_permission()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_lists_the_date_as_localized_only_when_the_localization_owns_it()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
            'de' => ['url' => 'http://localhost/de/', 'locale' => 'de'],
        ]);

        $blueprint = Blueprint::makeFromFields([
            'foo' => ['type' => 'text', 'localizable' => true],
            'date' => ['type' => 'date', 'localizable' => true],
        ])->setHandle('test');
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['test' => $blueprint]));

        Collection::make('blog')->dated(true)->sites(['en', 'fr', 'de'])->save();

        $this->setTestRoles(['test' => ['access cp', 'view blog entries', 'access fr site', 'access de site']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $origin = EntryFactory::id('1')->slug('test')->collection('blog')->locale('en')->date('2010-12-25')->data(['blueprint' => 'test', 'title' => 'Title'])->create();
        $ownDate = EntryFactory::id('2')->slug('test')->collection('blog')->locale('fr')->origin($origin)->date('2015-03-10')->data(['blueprint' => 'test'])->create();
        $inheritedDate = EntryFactory::id('3')->slug('test')->collection('blog')->locale('de')->origin($origin)->data(['blueprint' => 'test'])->create();

        $this
            ->actingAs($user)
            ->get($ownDate->editUrl())
            ->assertInertia(fn (Assert $page) => $page
                ->component('entries/Edit')
                ->where('localizedFields', fn ($fields) => in_array('date', $fields->all())));

        $this
            ->actingAs($user)
            ->get($inheritedDate->editUrl())
            ->assertInertia(fn (Assert $page) => $page
                ->component('entries/Edit')
                ->where('localizedFields', fn ($fields) => ! in_array('date', $fields->all())));
    }
}
