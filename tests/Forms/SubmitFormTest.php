<?php

namespace Tests\Forms;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionFinalized;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Form;
use Statamic\Forms\SendEmails;
use Statamic\Forms\SubmitForm;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubmitFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = tap(Form::make('contact')->honeypot('winnie')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                        ['handle' => 'email', 'field' => ['type' => 'email', 'validate' => 'required']],
                        ['handle' => 'message', 'field' => ['type' => 'long_answer']],
                    ],
                ],
            ],
        ]))->save();
    }

    public function tearDown(): void
    {
        $this->form->submissions()->each->delete();

        parent::tearDown();
    }

    private function action(): SubmitForm
    {
        return app(SubmitForm::class)->form($this->form);
    }

    #[Test]
    public function it_submits_a_form_successfully()
    {
        Bus::fake();

        $submission = $this->action()->submit(
            data: ['name' => 'Test User', 'email' => 'test@example.com', 'message' => 'Hello'],
        );

        $this->assertNotNull($submission);
        $this->assertEquals('Test User', $submission->get('name'));
        $this->assertEquals('test@example.com', $submission->get('email'));
        $this->assertEquals('Hello', $submission->get('message'));
    }

    #[Test]
    public function it_saves_submission_when_store_is_enabled()
    {
        Bus::fake();

        $this->assertEmpty($this->form->submissions());

        $this->action()->submit(
            data: ['email' => 'test@example.com'],
        );

        $this->assertCount(1, $this->form->submissions());
    }

    #[Test]
    public function it_dispatches_submission_created_event_when_store_is_disabled()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class]);

        $this->form->store(false);
        $this->form->save();

        $this->action()->submit(
            data: ['email' => 'test@example.com'],
        );

        Event::assertDispatched(SubmissionCreated::class);
        $this->assertEmpty($this->form->submissions());
    }

    #[Test]
    public function it_throws_silent_failure_exception_when_honeypot_is_filled()
    {
        $this->expectException(SilentFormFailureException::class);

        $this->action()->submit(
            data: ['email' => 'test@example.com', 'winnie' => 'the pooh'],
        );
    }

    #[Test]
    public function it_dispatches_form_submitted_event()
    {
        Bus::fake();
        Event::fake([FormSubmitted::class]);

        $this->action()->submit(
            data: ['email' => 'test@example.com'],
        );

        Event::assertDispatched(FormSubmitted::class, function ($event) {
            return $event->submission->get('email') === 'test@example.com';
        });
    }

    #[Test]
    public function it_throws_silent_failure_exception_when_event_listener_returns_false()
    {
        Event::listen(FormSubmitted::class, fn () => false);

        try {
            $this->action()->submit(
                data: ['email' => 'test@example.com'],
            );

            $this->fail('Expected SilentFormFailureException was not thrown');
        } catch (SilentFormFailureException $e) {
            $this->assertNotNull($e->submission());
        }
    }

    #[Test]
    public function it_throws_validation_exception_from_event_listener()
    {
        Event::listen(FormSubmitted::class, function () {
            throw ValidationException::withMessages(['custom' => 'Custom validation error']);
        });

        $this->expectException(ValidationException::class);

        $this->action()->submit(
            data: ['email' => 'test@example.com'],
        );
    }

    #[Test]
    public function it_dispatches_send_emails()
    {
        Bus::fake();

        $this->action()->submit(
            data: ['email' => 'test@example.com'],
        );

        Bus::assertDispatched(SendEmails::class);
    }

    #[Test]
    public function it_throws_validation_exception_when_validation_fails()
    {
        $this->expectException(ValidationException::class);

        $this->action()->validate(
            data: ['name' => 'Test'], // missing required email
        );
    }

    #[Test]
    public function it_throws_validation_exception_with_field_errors()
    {
        try {
            $this->action()->validate(data: ['name' => 'Test']);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }

    #[Test]
    public function validation_passes_with_valid_data()
    {
        $this->action()->validate(data: ['email' => 'test@example.com']);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_does_not_persist_a_submission_when_validation_fails()
    {
        $this->assertEmpty($this->form->submissions());

        try {
            $this->action()->submit(data: ['name' => 'Test']); // missing required email
        } catch (ValidationException $e) {
            // Expected
        }

        $this->assertEmpty($this->form->submissions());
    }

    #[Test]
    public function it_scopes_validation_to_the_given_fields()
    {
        // The email field is required, but scoping validation to "name" only
        // means the missing email shouldn't cause a validation failure.
        $this->action()->validate(data: ['name' => 'Test'], only: ['name']);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_still_validates_scoped_fields()
    {
        $this->expectException(ValidationException::class);

        $this->action()->validate(data: ['email' => 'not-an-email'], only: ['email']);
    }

    #[Test]
    public function it_can_resume_an_incomplete_submission()
    {
        Bus::fake();

        $draft = tap($this->form->makeSubmission()->data(['name' => 'Olaf', 'email' => 'old@example.com'])->asPartial())->save();

        $this->assertCount(1, $this->form->submissions());
        $this->assertTrue($this->form->submission($draft->id())->isPartial());

        $submission = $this->action()->resume($draft)->submit(
            data: ['email' => 'new@example.com'],
        );

        // Ensures the same submission is completed (eg. it didn't create a new one).
        $this->assertEquals($draft->id(), $submission->id());
        $this->assertCount(1, $this->form->submissions());

        $stored = $this->form->submission($draft->id());
        $this->assertFalse($stored->isPartial());

        // A field present in the request should update the existing value.
        $this->assertEquals('new@example.com', $stored->get('email'));

        // A field _not_ present in the request should be persisted.
        $this->assertEquals('Olaf', $stored->get('name'));
    }

    #[Test]
    public function it_dispatches_created_event_once_when_completing_a_resumed_submission()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class]);

        $draft = tap($this->form->makeSubmission()->data(['email' => 'old@example.com'])->asPartial())->save();

        $this->action()->resume($draft)->submit(
            data: ['email' => 'new@example.com'],
        );

        Event::assertDispatched(SubmissionCreated::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);
    }

    #[Test]
    public function it_uploads_files()
    {
        Bus::fake();
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'email', 'field' => ['type' => 'email', 'validate' => 'required']],
                        ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                    ],
                ],
            ],
        ]), fn ($f) => $f->save());

        $submission = app(SubmitForm::class)->form($form)->submit(
            data: ['email' => 'test@example.com'],
            files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
        );

        Storage::disk('avatars')->assertExists('avatar.jpg');

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_removes_uploaded_assets_on_silent_failure()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->honeypot('winnie')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                        ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                    ],
                ],
            ],
        ]), fn ($f) => $f->save());

        try {
            app(SubmitForm::class)->form($form)->submit(
                data: ['email' => 'test@example.com', 'winnie' => 'the pooh'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );
        } catch (SilentFormFailureException $e) {
            // Expected
        }

        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }

    #[Test]
    public function it_removes_uploaded_assets_when_event_listener_returns_false()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                        ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                    ],
                ],
            ],
        ]), fn ($f) => $f->save());

        Event::listen(FormSubmitted::class, function () {
            return false;
        });

        try {
            app(SubmitForm::class)->form($form)->submit(
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );
        } catch (SilentFormFailureException $e) {
            // Expected
        }

        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }

    #[Test]
    public function it_removes_uploaded_assets_on_validation_exception()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                        ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                    ],
                ],
            ],
        ]), fn ($f) => $f->save());

        Event::listen(FormSubmitted::class, function () {
            throw ValidationException::withMessages(['custom' => 'Error']);
        });

        try {
            app(SubmitForm::class)->form($form)->submit(
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );
        } catch (ValidationException $e) {
            // Expected
        }

        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }

    #[Test]
    public function a_precognitive_success_does_not_persist_a_submission()
    {
        Bus::fake();

        $this->assertEmpty($this->form->submissions());

        $this
            ->withPrecognition()
            ->withHeaders(['Precognition-Validate-Only' => 'email'])
            ->postJson('/!/forms/contact', ['email' => 'test@example.com'])
            ->assertNoContent()
            ->assertHeader('Precognition-Success', 'true');

        $this->assertEmpty($this->form->submissions());
        Bus::assertNotDispatched(SendEmails::class);
    }

    #[Test]
    public function it_saves_a_draft_without_completing_the_submission()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class, SubmissionFinalized::class]);

        $this->assertEmpty($this->form->submissions());

        $submission = $this->action()->asPartial()->submit(
            data: ['email' => 'test@example.com'],
        );

        $this->assertCount(1, $this->form->submissions());
        $this->assertTrue($this->form->submission($submission->id())->isPartial());
        $this->assertEquals('test@example.com', $submission->get('email'));

        // The partial submission record is created, but it isn't finalized.
        Event::assertDispatched(SubmissionCreated::class);
        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(SendEmails::class);
    }

    #[Test]
    public function it_merges_into_an_existing_draft_when_saving()
    {
        Bus::fake();

        $draft = tap($this->form->makeSubmission()->data(['name' => 'Olaf'])->asPartial())->save();

        $this->assertCount(1, $this->form->submissions());

        $submission = $this->action()->resume($draft)->asPartial()->submit(
            data: ['email' => 'new@example.com'],
        );

        // The same draft is updated rather than a new submission being created.
        $this->assertEquals($draft->id(), $submission->id());
        $this->assertCount(1, $this->form->submissions());

        $stored = $this->form->submission($draft->id());
        $this->assertTrue($stored->isPartial());

        // Earlier-page values are preserved while the new page's values are merged in.
        $this->assertEquals('Olaf', $stored->get('name'));
        $this->assertEquals('new@example.com', $stored->get('email'));
    }

    #[Test]
    public function it_scopes_a_draft_save_to_the_given_fields()
    {
        Bus::fake();

        // The email field is required, but scoping the draft save to "name" only
        // means the missing email shouldn't cause a validation failure.
        $submission = $this->action()->asPartial()->submit(
            data: ['name' => 'Test'],
            only: ['name'],
        );

        $stored = $this->form->submission($submission->id());
        $this->assertTrue($stored->isPartial());
        $this->assertEquals('Test', $stored->get('name'));
    }

    #[Test]
    public function it_completes_a_resumed_draft()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class]);

        $draft = tap($this->form->makeSubmission()->data(['name' => 'Olaf', 'email' => 'old@example.com'])->asPartial())->save();

        $this->assertTrue($this->form->submission($draft->id())->isPartial());

        $submission = $this->action()->resume($draft)->submit(
            data: ['email' => 'new@example.com'],
            only: ['name', 'email'],
        );

        // The same draft is promoted rather than a new submission being created.
        $this->assertEquals($draft->id(), $submission->id());
        $this->assertCount(1, $this->form->submissions());

        $stored = $this->form->submission($draft->id());
        $this->assertFalse($stored->isPartial());
        $this->assertEquals('new@example.com', $stored->get('email'));
        $this->assertEquals('Olaf', $stored->get('name'));

        // The completion events fire exactly once.
        Event::assertDispatched(SubmissionCreated::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);
    }

    #[Test]
    public function it_runs_the_gate_when_completing_a_resumed_draft()
    {
        $draft = tap($this->form->makeSubmission()->data(['email' => 'old@example.com'])->asPartial())->save();

        // A listener returning false silently aborts completion, proving the gate runs.
        Event::listen(FormSubmitted::class, fn () => false);

        try {
            $this->action()->resume($draft)->submit(
                data: ['email' => 'new@example.com'],
            );

            $this->fail('Expected SilentFormFailureException was not thrown');
        } catch (SilentFormFailureException $e) {
            $this->assertNotNull($e->submission());
        }

        // The draft stays incomplete since completion was silently aborted.
        $this->assertTrue($this->form->submission($draft->id())->isPartial());
    }
}
