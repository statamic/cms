<?php

namespace Tests\Feature\Forms;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormSubmitted;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\SendEmails;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GenerateFakeSubmissionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function authorized_users_can_generate_a_fake_submission()
    {
        $form = $this->makeForm('contact');
        $user = $this->userWithConfigureFormsPermission();

        $this->assertEquals(0, $form->querySubmissions()->count());

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertOk();

        $this->assertEquals(1, $form->querySubmissions()->count());
        $this->assertTrue((bool) $form->querySubmissions()->first()->get('_fake'));
    }

    #[Test]
    public function super_users_can_generate_a_fake_submission()
    {
        $form = $this->makeForm('contact');
        $user = User::make()->makeSuper()->save();

        $this->assertEquals(0, $form->querySubmissions()->count());

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertOk();

        $this->assertEquals(1, $form->querySubmissions()->count());
    }

    #[Test]
    public function unauthorized_users_cannot_generate_a_fake_submission()
    {
        $form = $this->makeForm('contact');
        $user = $this->userWithoutViewPermission();

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function users_with_view_submissions_permission_cannot_generate_a_fake_submission()
    {
        $form = $this->makeForm('contact');
        $user = $this->userWithViewPermission($form->handle());

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertEquals(0, $form->querySubmissions()->count());
    }

    #[Test]
    public function cp_only_mode_does_not_dispatch_full_pipeline_side_effects()
    {
        Event::fake([FormSubmitted::class]);
        Bus::fake();

        $form = $this->makeForm('contact');
        $user = $this->userWithConfigureFormsPermission();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertOk();

        Event::assertNotDispatched(FormSubmitted::class);
        Bus::assertNotDispatched(SendEmails::class);
        $this->assertEquals(1, $form->querySubmissions()->count());
    }

    #[Test]
    public function full_pipeline_mode_dispatches_frontend_side_effects()
    {
        Event::fake([FormSubmitted::class]);
        Bus::fake();

        $form = $this->makeForm('contact');
        $user = $this->userWithConfigureFormsPermission();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'full_pipeline'])
            ->assertOk();

        Event::assertDispatched(FormSubmitted::class);
        Bus::assertDispatched(SendEmails::class);
        $this->assertEquals(1, $form->querySubmissions()->count());
    }

    #[Test]
    public function unsupported_fields_fallback_to_null()
    {
        $form = $this->makeForm('contact');
        $user = $this->userWithConfigureFormsPermission();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertOk();

        $submission = $form->querySubmissions()->first();

        $this->assertNotNull($submission->get('name'));
        $this->assertNull($submission->get('rich_content'));
    }

    #[Test]
    public function it_cannot_generate_fake_submissions_when_disabled_in_form_configuration()
    {
        $form = $this->makeForm('contact', false);
        $user = $this->userWithConfigureFormsPermission();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertStatus(403);

        $this->assertEquals(0, $form->querySubmissions()->count());
    }

    private function makeForm(string $handle, bool $generateFakeSubmissions = true)
    {
        Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
            'message' => ['type' => 'textarea'],
            'category' => [
                'type' => 'select',
                'options' => [
                    'support' => 'Support',
                    'sales' => 'Sales',
                ],
            ],
            'tags' => [
                'type' => 'checkboxes',
                'options' => [
                    'a' => 'A',
                    'b' => 'B',
                    'c' => 'C',
                ],
            ],
            'rich_content' => ['type' => 'bard'],
        ])->setHandle($handle)->setNamespace('forms')->save();

        /** @var \Statamic\Forms\Form $form */
        $form = Form::make($handle)->title('Contact')->merge([
            'generate_fake_submissions' => $generateFakeSubmissions,
        ]);

        $form->save();

        return $form;
    }

    private function userWithViewPermission(string $formHandle)
    {
        $this->setTestRoles(['test' => ['access cp', "view {$formHandle} form submissions"]]);

        return User::make()->assignRole('test')->save();
    }

    private function userWithoutViewPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return User::make()->assignRole('test')->save();
    }

    private function userWithConfigureFormsPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);

        return User::make()->assignRole('test')->save();
    }
}
