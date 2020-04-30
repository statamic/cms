<?php

namespace Tests\Feature\Fieldsets;

use Facades\Statamic\Fields\FieldsetRepository;
use Statamic\Facades;
use Tests\Fakes\FakeFieldsetRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreFieldsetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        FieldsetRepository::swap(new FakeFieldsetRepository);
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), [
                'handle' => 'Test',
                'title' => 'Updated',
                'fields' => [],
            ])
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertNull(Facades\Fieldset::find('test'));
    }

    /** @test */
    public function fieldset_gets_created()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());

        $this
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), ['title' => 'Test'])
            ->assertRedirect(cp_route('fieldsets.edit', 'test'))
            ->assertSessionHas('success');

        $this->assertCount(1, Facades\Fieldset::all());
        $this->assertEquals([
            'title' => 'Test',
            'fields' => [],
        ], Facades\Fieldset::find('test')->contents());
    }

    /** @test */
    public function title_is_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('fieldsets.store'), ['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');
    }
}
