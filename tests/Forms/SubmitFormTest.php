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
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
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

        $this->form = tap(Form::make('contact')->honeypot('winnie'), function ($form) {
            $form->save();
        });

        $this->form->blueprint()->ensureField('name', ['type' => 'text'])->save();
        $this->form->blueprint()->ensureField('email', ['type' => 'text', 'validate' => 'required|email'])->save();
        $this->form->blueprint()->ensureField('message', ['type' => 'textarea'])->save();
    }

    public function tearDown(): void
    {
        $this->form->submissions()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_submits_a_form_successfully()
    {
        Bus::fake();

        $submission = app(SubmitForm::class)->submit(
            form: $this->form,
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

        app(SubmitForm::class)->submit(
            form: $this->form,
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

        app(SubmitForm::class)->submit(
            form: $this->form,
            data: ['email' => 'test@example.com'],
        );

        Event::assertDispatched(SubmissionCreated::class);
        $this->assertEmpty($this->form->submissions());
    }

    #[Test]
    public function it_throws_silent_failure_exception_when_honeypot_is_filled()
    {
        $this->expectException(SilentFormFailureException::class);

        app(SubmitForm::class)->submit(
            form: $this->form,
            data: ['email' => 'test@example.com', 'winnie' => 'the pooh'],
        );
    }

    #[Test]
    public function it_includes_submission_in_silent_failure_exception()
    {
        try {
            app(SubmitForm::class)->submit(
                form: $this->form,
                data: ['email' => 'test@example.com', 'winnie' => 'the pooh'],
            );

            $this->fail('Expected SilentFormFailureException was not thrown');
        } catch (SilentFormFailureException $e) {
            $this->assertNotNull($e->submission());
            $this->assertEquals($this->form, $e->submission()->form());
        }
    }

    #[Test]
    public function it_dispatches_form_submitted_event()
    {
        Bus::fake();
        Event::fake([FormSubmitted::class]);

        app(SubmitForm::class)->submit(
            form: $this->form,
            data: ['email' => 'test@example.com'],
        );

        Event::assertDispatched(FormSubmitted::class, function ($event) {
            return $event->submission->get('email') === 'test@example.com';
        });
    }

    #[Test]
    public function it_throws_silent_failure_exception_when_event_listener_returns_false()
    {
        Event::listen(FormSubmitted::class, function () {
            return false;
        });

        try {
            app(SubmitForm::class)->submit(
                form: $this->form,
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

        app(SubmitForm::class)->submit(
            form: $this->form,
            data: ['email' => 'test@example.com'],
        );
    }

    #[Test]
    public function it_dispatches_send_emails()
    {
        Bus::fake();

        app(SubmitForm::class)->submit(
            form: $this->form,
            data: ['email' => 'test@example.com'],
            site: Site::default(),
        );

        Bus::assertDispatched(SendEmails::class);
    }

    #[Test]
    public function it_creates_a_validator()
    {
        $validator = app(SubmitForm::class)->validator(
            form: $this->form,
            data: ['name' => 'Test'],
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[Test]
    public function validator_passes_with_valid_data()
    {
        $validator = app(SubmitForm::class)->validator(
            form: $this->form,
            data: ['email' => 'test@example.com'],
        );

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function it_throws_validation_exception_when_validation_fails()
    {
        $this->expectException(ValidationException::class);

        app(SubmitForm::class)->submit(
            form: $this->form,
            data: ['name' => 'Test'], // missing required email
        );
    }

    #[Test]
    public function it_uploads_files()
    {
        Bus::fake();
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads'), fn ($f) => $f->save());
        $form->blueprint()->ensureField('email', ['type' => 'text', 'validate' => 'required|email'])->save();
        $form->blueprint()->ensureField('avatar', ['type' => 'assets', 'container' => 'avatars'])->save();

        $submission = app(SubmitForm::class)->submit(
            form: $form,
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

        $form = tap(Form::make('uploads')->honeypot('winnie'), fn ($f) => $f->save());
        $form->blueprint()->ensureField('email', ['type' => 'text'])->save();
        $form->blueprint()->ensureField('avatar', ['type' => 'assets', 'container' => 'avatars'])->save();

        try {
            app(SubmitForm::class)->submit(
                form: $form,
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

        $form = tap(Form::make('uploads'), fn ($f) => $f->save());
        $form->blueprint()->ensureField('email', ['type' => 'text'])->save();
        $form->blueprint()->ensureField('avatar', ['type' => 'assets', 'container' => 'avatars'])->save();

        Event::listen(FormSubmitted::class, function () {
            return false;
        });

        try {
            app(SubmitForm::class)->submit(
                form: $form,
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

        $form = tap(Form::make('uploads'), fn ($f) => $f->save());
        $form->blueprint()->ensureField('email', ['type' => 'text'])->save();
        $form->blueprint()->ensureField('avatar', ['type' => 'assets', 'container' => 'avatars'])->save();

        Event::listen(FormSubmitted::class, function () {
            throw ValidationException::withMessages(['custom' => 'Error']);
        });

        try {
            app(SubmitForm::class)->submit(
                form: $form,
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );
        } catch (ValidationException $e) {
            // Expected
        }

        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }
}
