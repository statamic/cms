<?php

namespace Tests\Forms;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Form;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RestrictionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $submissionId = 0;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-06 12:00:00');
    }

    protected function makeForm(array $data = [])
    {
        return tap(Form::make('contact')->data($data))->save();
    }

    protected function makeSubmittableForm(array $data = [])
    {
        return tap(
            Form::make('contact')
                ->formFields(['sections' => [['fields' => [
                    ['handle' => 'email', 'field' => ['type' => 'text']],
                ]]]])
                ->data($data)
        )->save();
    }

    protected function submit($form, $count = 1, $partial = false)
    {
        for ($i = 0; $i < $count; $i++) {
            $submission = $form->makeSubmission()->id(Carbon::now()->timestamp - $this->submissionId++);

            if ($partial) {
                $submission->set('partial', true);
            }

            $submission->save();
        }
    }

    #[Test]
    public function a_form_with_no_restrictions_is_not_restricted()
    {
        $restrictions = $this->makeForm()->restrictions();

        $this->assertFalse($restrictions->restricted());
        $this->assertNull($restrictions->message());
    }

    #[Test]
    public function it_is_restricted_after_the_close_date()
    {
        $restrictions = $this->makeForm(['close_date' => '2026-07-01 09:00'])->restrictions();

        $this->assertTrue($restrictions->restricted());
        $this->assertEquals('This form is no longer accepting submissions.', $restrictions->message());
    }

    #[Test]
    public function it_is_not_restricted_before_the_close_date()
    {
        $restrictions = $this->makeForm(['close_date' => '2026-07-10 09:00'])->restrictions();

        $this->assertFalse($restrictions->restricted());
    }

    #[Test]
    public function it_is_restricted_when_the_submission_limit_is_reached()
    {
        $form = $this->makeForm(['submission_limit' => 2]);

        $this->submit($form, 1);
        $this->assertFalse($form->restrictions()->restricted());

        $this->submit($form, 1);
        $this->assertTrue($form->restrictions()->restricted());
        $this->assertEquals('This form is no longer accepting submissions.', $form->restrictions()->message());
    }

    #[Test]
    public function partial_submissions_are_excluded_from_the_limit()
    {
        $form = $this->makeForm(['submission_limit' => 2]);

        $this->submit($form, 5, partial: true);
        $this->submit($form, 1);

        $this->assertFalse($form->restrictions()->restricted());
    }

    #[Test]
    public function the_submission_limit_can_reset_each_day()
    {
        $form = $this->makeForm(['submission_limit' => 2, 'submission_limit_period' => 'day']);

        Carbon::setTestNow('2026-07-05 23:00:00');
        $this->submit($form, 2);
        $this->assertTrue($form->restrictions()->restricted());

        Carbon::setTestNow('2026-07-06 00:30:00');
        $this->assertFalse($form->restrictions()->restricted());
    }

    #[Test]
    public function it_is_restricted_when_login_is_required_and_the_visitor_is_logged_out()
    {
        $form = $this->makeForm(['require_login' => true]);

        $this->assertTrue($form->restrictions()->restricted());
        $this->assertEquals('You must be logged in to submit this form.', $form->restrictions()->message());
    }

    #[Test]
    public function it_is_not_restricted_when_login_is_required_and_the_visitor_is_logged_in()
    {
        $form = $this->makeForm(['require_login' => true]);

        $this->actingAs(User::make()->save());

        $this->assertFalse($form->restrictions()->restricted());
    }

    #[Test]
    public function the_closed_message_takes_precedence_over_the_login_message()
    {
        $form = $this->makeForm([
            'submission_limit' => 1,
            'require_login' => true,
        ]);

        $this->submit($form, 1);

        $this->assertEquals('This form is no longer accepting submissions.', $form->restrictions()->message());
    }

    #[Test]
    public function configured_messages_override_the_defaults()
    {
        $form = $this->makeForm([
            'close_date' => '2026-07-01 09:00',
            'closed_message' => 'Sorry, we are closed.',
        ]);

        $this->assertEquals('Sorry, we are closed.', $form->restrictions()->message());
    }

    #[Test]
    public function the_status_is_open_by_default()
    {
        $this->assertEquals('open', $this->makeForm()->status());
    }

    #[Test]
    public function the_status_is_closed_after_the_close_date()
    {
        $this->assertEquals('closed', $this->makeForm(['close_date' => '2026-07-01 09:00'])->status());
    }

    #[Test]
    public function the_status_is_limit_reached_when_the_submission_limit_is_reached()
    {
        $form = $this->makeForm(['submission_limit' => 1]);

        $this->submit($form, 1);

        Blink::flush();

        $this->assertEquals('limit_reached', $form->status());
    }

    #[Test]
    public function requiring_login_does_not_affect_the_status()
    {
        $this->assertEquals('open', $this->makeForm(['require_login' => true])->status());
    }

    #[Test]
    public function the_status_is_augmented()
    {
        $form = $this->makeForm(['close_date' => '2026-07-01 09:00']);

        $this->assertEquals('closed', $form->toAugmentedArray()['status']);
    }

    #[Test]
    public function it_blocks_a_restricted_submission_and_creates_nothing()
    {
        $form = $this->makeSubmittableForm(['require_login' => true]);

        $this->post('/!/forms/contact', ['email' => 'foo@bar.com'])
            ->assertSessionHasErrors(['*'], null, 'form.contact')
            ->assertLocation('/');

        $this->assertCount(0, $form->submissions());
    }

    #[Test]
    public function it_returns_a_400_with_the_message_for_ajax_requests()
    {
        $this->makeSubmittableForm(['require_login' => true]);

        $this->post('/!/forms/contact', ['email' => 'foo@bar.com'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(400)
            ->assertJsonPath('errors.0', 'You must be logged in to submit this form.');
    }

    #[Test]
    public function precognitive_requests_do_not_create_submissions_on_a_restricted_form()
    {
        $form = $this->makeSubmittableForm(['require_login' => true]);

        $this->withPrecognition()
            ->post('/!/forms/contact', ['email' => 'foo@bar.com'])
            ->assertNoContent();

        $this->assertCount(0, $form->submissions());
    }

    #[Test]
    public function a_logged_in_visitor_can_submit_a_form_that_requires_login()
    {
        $form = $this->makeSubmittableForm(['require_login' => true]);

        $this->actingAs(User::make()->email('a@b.com')->save());

        $this->post('/!/forms/contact', ['email' => 'foo@bar.com'])->assertLocation('/');

        $this->assertCount(1, $form->submissions());
    }

    #[Test]
    public function an_open_form_accepts_submissions()
    {
        $form = $this->makeSubmittableForm();

        $this->post('/!/forms/contact', ['email' => 'foo@bar.com'])->assertLocation('/');

        $this->assertCount(1, $form->submissions());
    }

    #[Test]
    public function it_adds_restriction_variables_to_the_tag()
    {
        $this->makeSubmittableForm(['require_login' => true]);

        $output = (string) Parse::template(
            '{{ form:contact }}{{ if restricted }}is-restricted{{ /if }} status:{{ status }} msg:{{ restriction_message }}{{ /form:contact }}',
            [],
            trusted: true
        );

        $this->assertStringContainsString('is-restricted', $output);
        $this->assertStringContainsString('status:open', $output);
        $this->assertStringContainsString('msg:You must be logged in to submit this form.', $output);
    }

    #[Test]
    public function an_open_form_is_not_restricted_in_the_tag()
    {
        $this->makeSubmittableForm();

        $output = (string) Parse::template(
            '{{ form:contact }}{{ if restricted }}is-restricted{{ /if }} status:{{ status }}{{ /form:contact }}',
            [],
            trusted: true
        );

        $this->assertStringNotContainsString('is-restricted', $output);
        $this->assertStringContainsString('status:open', $output);
    }
}
