<?php

namespace Tests\Feature\Globals;

use Facades\Tests\Factories\GlobalFactory;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalVariablesSaved;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\GlobalVariables;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateGlobalsTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_edit_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $global = GlobalFactory::handle('test')->create();

        $this
            ->actingAs($user)
            ->patchJson($global->updateUrl(), ['title' => 'Testing'])
            ->assertForbidden();
    }

    #[Test]
    public function it_updates_global_set()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure globals']]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalFactory::handle('test')->title('Test')->create();

        Event::fake();

        $this
            ->actingAs($user)
            ->patchJson($global->updateUrl(), ['title' => 'Testing'])
            ->assertSuccessful();

        $global = GlobalSet::find('test');
        $this->assertEquals('Testing', $global->title());

        Event::assertDispatched(GlobalSetSaved::class, function ($event) {
            return $event->globals->handle() === 'test';
        });

        Event::assertNotDispatched(GlobalVariablesSaved::class, function ($event) {
            return $event->variables->globalSet()->handle() === 'test';
        });
    }

    #[Test]
    public function it_updates_global_set_with_sites()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            'it' => ['name' => 'Italian', 'locale' => 'it_IT', 'url' => 'http://test.com/it/'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure globals']]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalFactory::handle('test')->sites(['en' => null, 'it' => 'en'])->create();

        $this->assertNotNull(GlobalVariables::find('test::en'));
        $this->assertNull(GlobalVariables::find('test::fr'));
        $this->assertNull(GlobalVariables::find('test::de'));
        $this->assertNotNull(GlobalVariables::find('test::it'));

        $this
            ->actingAs($user)
            ->patchJson($global->updateUrl(), [
                'title' => 'test',
                'sites' => [
                    ['name' => 'English', 'handle' => 'en', 'enabled' => true, 'origin' => null],
                    ['name' => 'French', 'handle' => 'fr', 'enabled' => true, 'origin' => null],
                    ['name' => 'German', 'handle' => 'de', 'enabled' => true, 'origin' => 'fr'],
                    ['name' => 'Italian', 'handle' => 'it', 'enabled' => false, 'origin' => null],
                ],
            ])
            ->assertSuccessful();

        $global = GlobalSet::find('test');

        // Keep English, add French, add German with an origin, remove Italian.
        $this->assertEquals([
            'en' => null,
            'fr' => null,
            'de' => 'fr',
        ], $global->sites()->all());

        $this->assertNotNull(GlobalVariables::find('test::en'));
        $this->assertNotNull(GlobalVariables::find('test::fr'));
        $this->assertNotNull(GlobalVariables::find('test::de'));
        $this->assertNull(GlobalVariables::find('test::it'));
    }
}
