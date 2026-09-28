<?php

namespace Tests\Feature\Fieldsets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Fieldset;
use Tests\Fakes\FakeFieldsetRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditFieldsetTest extends TestCase
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
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($fieldset->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_provides_the_fieldset()
    {
        $this->withoutExceptionHandling();
        $user = Facades\User::make()->makeSuper()->save();
        $fieldset = (new Fieldset)->setHandle('test')->setContents([
            'title' => 'Test',
            'fields' => [
                ['handle' => 'title', 'field' => ['type' => 'text']],
            ],
        ])->save();

        $this
            ->actingAs($user)
            ->get($fieldset->editUrl())
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('initialFieldset.handle', $fieldset->handle())
                ->where('initialFieldset.sections.0.fields.0.handle', 'title')
            );
    }

    #[Test]
    public function it_provides_sectioned_fieldsets()
    {
        $user = Facades\User::make()->makeSuper()->save();
        $fieldset = (new Fieldset)->setHandle('test')->setContents([
            'title' => 'Test',
            'sections' => [
                [
                    'display' => 'SEO',
                    'fields' => [
                        ['handle' => 'meta_title', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ])->save();

        $this
            ->actingAs($user)
            ->get($fieldset->editUrl())
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('initialFieldset.sections.0.display', 'SEO')
                ->where('initialFieldset.sections.0.fields.0.handle', 'meta_title')
            );
    }
}
