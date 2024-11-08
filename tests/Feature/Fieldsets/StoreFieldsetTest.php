<?php

namespace Tests\Feature\Fieldsets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Fieldset as FieldsetRepository;
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

    #[Test]
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

    #[Test]
    public function fieldset_gets_created()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());

        $this
            ->actingAs($user)
            ->submit()
            ->assertOk()
            ->assertJson(['redirect' => cp_route('fieldsets.edit', 'test')])
            ->assertSessionHas('success');

        $this->assertCount(1, Facades\Fieldset::all());
        $fieldset = Facades\Fieldset::find('test');
        $this->assertEquals([
            'title' => 'Test',
        ], $fieldset->contents());
        $this->assertEquals('test', $fieldset->handle());
    }

    #[Test]
    public function title_is_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit(['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');
    }

    #[Test]
    public function handle_is_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit(['handle' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('handle');
    }

    #[Test]
    public function handle_must_be_alpha_dash()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Fieldset::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit(['handle' => 'two words'])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('handle');
    }

    private function submit($params = [])
    {
        return $this->post(cp_route('fieldsets.store'), $this->validParams($params));
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Test',
            'handle' => 'test',
        ], $overrides);
    }
}
