<?php

namespace Tests\Forms;

use Carbon\Carbon;
use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AccessTest extends TestCase
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

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);
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

    protected function submit($form, $count = 1, $partial = false, $entry = null)
    {
        for ($i = 0; $i < $count; $i++) {
            $submission = $form->makeSubmission()->id(Carbon::now()->timestamp - $this->submissionId++);

            if ($partial) {
                $submission->set('partial', true);
            }

            if ($entry) {
                $submission->set('entry', $entry);
            }

            $submission->save();
        }
    }

    private function makeEntry(string $id, array $formValue): void
    {
        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'rsvp_form', 'field' => ['type' => 'form', 'max_items' => 1]],
        ]])->save();

        (new EntryFactory)->collection('events')->id($id)->slug($id)->data(['rsvp_form' => $formValue])->create();
    }

    #[Test]
    public function a_form_with_no_restrictions_is_not_restricted()
    {
        $form = $this->makeForm();

        $this->assertFalse($form->restricted());
        $this->assertNull($form->restrictionMessage());
    }

    #[Test]
    public function it_is_restricted_after_the_close_date()
    {
        $form = $this->makeForm(['close_date' => '2026-07-01 09:00']);

        $this->assertTrue($form->restricted());
        $this->assertEquals('This form is no longer accepting submissions.', $form->restrictionMessage());
    }

    #[Test]
    public function it_is_not_restricted_before_the_close_date()
    {
        $form = $this->makeForm(['close_date' => '2026-07-10 09:00']);

        $this->assertFalse($form->restricted());
    }

    #[Test]
    public function it_is_restricted_when_the_submission_limit_is_reached()
    {
        $form = $this->makeForm(['submission_limit' => 2]);

        $this->submit($form, 1);
        $this->assertFalse($form->restricted());

        $this->submit($form, 1);
        $this->assertTrue($form->restricted());
        $this->assertEquals('This form is no longer accepting submissions.', $form->restrictionMessage());
    }

    #[Test]
    public function the_submission_limit_is_scoped_per_entry_when_unique_instances_is_enabled()
    {
        $form = $this->makeForm(['submission_limit' => 2, 'unique_instances' => true]);

        $this->submit($form, 2, entry: 'event-1');
        $this->submit($form, 1, entry: 'event-2');

        $this->assertTrue($form->instance('event-1')->restricted());
        $this->assertEquals('limit_reached', $form->instance('event-1')->status());

        $this->assertFalse($form->instance('event-2')->restricted());
        $this->assertEquals('open', $form->instance('event-2')->status());
    }

    #[Test]
    public function an_entry_can_override_the_submission_limit()
    {
        $form = $this->makeForm(['submission_limit' => 5, 'unique_instances' => true]);

        $this->makeEntry('event-1', ['form' => 'contact', 'config' => ['submission_limit' => 1]]);

        $this->submit($form, 1, entry: 'event-1');

        $this->assertTrue($form->instance('event-1')->restricted());
        $this->assertFalse($form->restricted());
    }

    #[Test]
    public function an_entry_can_override_the_close_date_and_message()
    {
        $form = $this->makeForm(['unique_instances' => true]);

        $this->makeEntry('event-1', ['form' => 'contact', 'config' => [
            'close_date' => '2026-07-01 09:00',
            'closed_message' => 'This event is full.',
        ]]);

        $this->assertTrue($form->instance('event-1')->restricted());
        $this->assertEquals('This event is full.', $form->instance('event-1')->restrictionMessage());
        $this->assertFalse($form->restricted());
    }

    #[Test]
    public function an_entry_can_override_require_login_off()
    {
        $form = $this->makeForm(['require_login' => true, 'unique_instances' => true]);

        $this->makeEntry('event-1', ['form' => 'contact', 'config' => ['require_login' => false]]);

        $this->assertFalse($form->instance('event-1')->restricted());
        $this->assertTrue($form->restricted());
    }

    #[Test]
    public function overrides_from_an_entry_using_a_different_form_are_ignored()
    {
        $form = $this->makeForm(['unique_instances' => true]);

        $this->makeEntry('event-1', ['form' => 'another_form', 'config' => ['close_date' => '2026-07-01 09:00']]);

        $this->assertFalse($form->instance('event-1')->restricted());
    }

    #[Test]
    public function partial_submissions_are_excluded_from_the_limit()
    {
        $form = $this->makeForm(['submission_limit' => 2]);

        $this->submit($form, 5, partial: true);
        $this->submit($form, 1);

        $this->assertFalse($form->restricted());
    }

    #[Test]
    public function the_submission_limit_can_reset_each_day()
    {
        $form = $this->makeForm(['submission_limit' => 2, 'submission_limit_period' => 'day']);

        Carbon::setTestNow('2026-07-05 23:00:00');
        $this->submit($form, 2);
        $this->assertTrue($form->restricted());

        Carbon::setTestNow('2026-07-06 00:30:00');
        $this->assertFalse($form->restricted());
    }

    #[Test]
    public function it_is_restricted_when_login_is_required_and_the_visitor_is_logged_out()
    {
        $form = $this->makeForm(['require_login' => true]);

        $this->assertTrue($form->restricted());
        $this->assertEquals('You must be logged in to submit this form.', $form->restrictionMessage());
    }

    #[Test]
    public function it_is_not_restricted_when_login_is_required_and_the_visitor_is_logged_in()
    {
        $form = $this->makeForm(['require_login' => true]);

        $this->actingAs(User::make()->save());

        $this->assertFalse($form->restricted());
    }

    #[Test]
    public function the_closed_message_takes_precedence_over_the_login_message()
    {
        $form = $this->makeForm([
            'submission_limit' => 1,
            'require_login' => true,
        ]);

        $this->submit($form, 1);

        $this->assertEquals('This form is no longer accepting submissions.', $form->restrictionMessage());
    }

    #[Test]
    public function configured_messages_override_the_defaults()
    {
        $form = $this->makeForm([
            'close_date' => '2026-07-01 09:00',
            'closed_message' => 'Sorry, we are closed.',
        ]);

        $this->assertEquals('Sorry, we are closed.', $form->restrictionMessage());
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
