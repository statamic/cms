<?php

namespace Tests\Forms;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionFinalized;
use Statamic\Exceptions\FormRestrictedException;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Form;
use Statamic\Forms\CreateAssetsFromFileUploads;
use Statamic\Forms\SendEmails;
use Statamic\Forms\SubmissionResult;
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

        // Page ids only exist when forms pro is installed; without it, pages are
        // flattened into sections and lose their ids. The action keys everything
        // (finalizing, field scoping, the next page) off page ids, so we fake it.
        Composer::shouldReceive('isInstalled')->andReturnFalse()->byDefault();
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturnTrue()->byDefault();

        $this->form = tap(Form::make('contact')->honeypot('winnie')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                                ['handle' => 'email', 'field' => ['type' => 'email', 'validate' => 'required']],
                                ['handle' => 'message', 'field' => ['type' => 'long_answer']],
                            ],
                        ],
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
        return app(SubmitForm::class)->form($this->form)->page('main');
    }

    private function multiPageForm()
    {
        return tap(Form::make('signup')->honeypot('winnie')->formFields([
            'pages' => [
                [
                    'id' => 'one',
                    'sections' => [
                        ['fields' => [['handle' => 'name', 'field' => ['type' => 'short_answer']]]],
                    ],
                ],
                [
                    'id' => 'two',
                    'sections' => [
                        ['fields' => [['handle' => 'email', 'field' => ['type' => 'email', 'validate' => 'required']]]],
                    ],
                ],
                [
                    'id' => 'three',
                    'sections' => [
                        ['fields' => [['handle' => 'message', 'field' => ['type' => 'long_answer']]]],
                    ],
                ],
            ],
        ]))->save();
    }

    private function multiPageFormWithLogic()
    {
        return tap(Form::make('signup')->formFields([
            'pages' => [
                [
                    'id' => 'one',
                    'rules' => [[
                        'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'skip']],
                        'destination' => 'three',
                    ]],
                    'sections' => [['fields' => [['handle' => 'name', 'field' => ['type' => 'short_answer']]]]],
                ],
                [
                    'id' => 'two',
                    'sections' => [['fields' => [['handle' => 'email', 'field' => ['type' => 'email']]]]],
                ],
                [
                    'id' => 'three',
                    'sections' => [['fields' => [['handle' => 'message', 'field' => ['type' => 'long_answer']]]]],
                ],
            ],
        ]))->save();
    }

    #[Test]
    public function it_submits_a_form_successfully()
    {
        Event::fake([FormSubmitted::class]);

        $result = $this->action()->submit(
            ['name' => 'Test User', 'email' => 'test@example.com', 'message' => 'Hello'],
        );

        $this->assertInstanceOf(SubmissionResult::class, $result);
        $this->assertTrue($result->isFinalized());
        $this->assertNull($result->nextPage);
        $this->assertEquals('Test User', $result->submission->get('name'));
        $this->assertEquals('test@example.com', $result->submission->get('email'));
        $this->assertEquals('Hello', $result->submission->get('message'));

        Event::assertDispatched(FormSubmitted::class, function ($event) {
            return $event->submission->get('email') === 'test@example.com';
        });
    }

    #[Test]
    public function it_saves_submission_when_store_is_enabled()
    {
        $this->assertEmpty($this->form->submissions());

        $this->action()->submit(['email' => 'test@example.com']);

        $this->assertCount(1, $this->form->submissions());
    }

    #[Test]
    public function it_finalizes_without_storing_when_store_is_disabled()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class, SubmissionFinalized::class]);

        $this->form->store(false);
        $this->form->save();

        $result = $this->action()->submit(['email' => 'test@example.com']);

        $this->assertTrue($result->isFinalized());
        $this->assertEmpty($this->form->submissions());
        Event::assertDispatched(SubmissionCreated::class);
        Event::assertDispatched(SubmissionFinalized::class);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertDispatched(SendEmails::class);
    }

    #[Test]
    public function validation_passes_with_valid_data()
    {
        $this->action()->validate(['email' => 'test@example.com']);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_throws_validation_exception_when_validation_fails()
    {
        $this->expectException(ValidationException::class);

        $this->action()->validate(['name' => 'Test']); // missing required email
    }

    #[Test]
    public function it_throws_validation_exception_with_field_errors()
    {
        try {
            $this->action()->validate(['name' => 'Test']);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }

    #[Test]
    public function it_does_not_persist_a_submission_when_validation_fails()
    {
        $this->assertEmpty($this->form->submissions());

        try {
            $this->action()->submit(['name' => 'Test']); // missing required email
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
        $this->action()->validate(['name' => 'Test'], only: ['name']);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_still_validates_scoped_fields()
    {
        $this->expectException(ValidationException::class);

        $this->action()->validate(['email' => 'not-an-email'], only: ['email']);
    }

    #[Test]
    public function it_throws_a_form_restricted_exception_when_the_form_is_restricted()
    {
        $this->form->data(['require_login' => true])->save();

        $this->expectException(FormRestrictedException::class);

        $this->action()->submit(['email' => 'test@example.com']);
    }

    #[Test]
    public function it_does_not_throw_when_validating_a_restricted_form()
    {
        $this->form->data(['require_login' => true])->save();

        $this->action()->validate(['email' => 'test@example.com']);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_throws_silent_failure_exception_when_honeypot_is_filled()
    {
        $this->expectException(SilentFormFailureException::class);

        $this->action()->submit(
            ['email' => 'test@example.com', 'winnie' => 'the pooh'],
        );
    }

    #[Test]
    public function it_throws_silent_failure_exception_when_event_listener_returns_false()
    {
        Event::listen(FormSubmitted::class, fn () => false);

        $action = $this->action();

        try {
            $action->submit(['email' => 'test@example.com']);

            $this->fail('Expected SilentFormFailureException was not thrown');
        } catch (SilentFormFailureException $e) {
            $this->assertNotNull($action->submission());
        }
    }

    #[Test]
    public function it_throws_validation_exception_from_event_listener()
    {
        Event::listen(FormSubmitted::class, function () {
            throw ValidationException::withMessages(['custom' => 'Custom validation error']);
        });

        $this->expectException(ValidationException::class);

        $this->action()->submit(['email' => 'test@example.com']);
    }

    #[Test]
    public function it_uploads_files()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = $this->uploadForm();

        app(SubmitForm::class)
            ->form($form)
            ->page('main')
            ->submit(
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );

        Storage::disk('avatars')->assertExists('avatar.jpg');

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_uploads_files_via_assets_fieldtype()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('avatar.png')->formFields([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'email']],
                ['handle' => 'avatar', 'field' => ['type' => 'assets', 'container' => 'avatars', 'max_files' => 1]],
            ],
        ]))->save();

        $result = app(SubmitForm::class)
            ->form($form)
            ->submit(
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );

        Storage::disk('avatars')->assertExists('avatar.jpg');
        $this->assertEquals('avatar.jpg', $result->submission->get('avatar'));

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_uploads_files_via_files_fieldtype()
    {
        Storage::fake('local');
        Bus::fake(); // Otherwise finalize's queued cleanup job removes it before we can look.

        $form = tap(Form::make('avatar.png')->formFields([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'email']],
                ['handle' => 'avatar', 'field' => ['type' => 'files', 'max_files' => 1]],
            ],
        ]))->save();

        $result = app(SubmitForm::class)
            ->form($form)
            ->submit(
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->create('resume.pdf', 10)]],
            );

        $path = $result->submission->get('avatar');

        $this->assertNotNull($path);
        Storage::disk('local')->assertExists('statamic/file-uploads/'.$path);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_does_not_revalidate_already_uploaded_files_when_resubmitting_their_page()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [
                            ['handle' => 'email', 'field' => ['type' => 'email']],
                            ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                            ['handle' => 'headshot', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                        ]],
                    ],
                ],
            ],
        ]))->save();

        $first = app(SubmitForm::class)
            ->form($form)
            ->page('main')
            ->submit(
                data: ['email' => 'test@example.com'],
                files: [
                    'avatar' => [UploadedFile::fake()->image('avatar.jpg')],
                    'headshot' => [UploadedFile::fake()->image('headshot.jpg')],
                ],
            );

        // Going back to the page and resubmitting resends each field's already-resolved value:
        // an array for `avatar` (no max_files), a plain string for `headshot` (max_files: 1).
        // Neither should be revalidated as though it were a fresh upload.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('main')
            ->resume($first->submission)
            ->submit(data: [
                'email' => 'test@example.com',
                'avatar' => $first->submission->get('avatar'),
                'headshot' => $first->submission->get('headshot'),
            ]);

        $this->assertTrue($result->isFinalized());

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_still_validates_a_freshly_uploaded_file_when_resubmitting_its_page()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [
                            ['handle' => 'email', 'field' => ['type' => 'email']],
                            ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                        ]],
                    ],
                ],
            ],
        ]))->save();

        $first = app(SubmitForm::class)
            ->form($form)
            ->page('main')
            ->submit(
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );

        // Resubmitting with a genuinely new file should still be validated as a fresh upload,
        // not skipped the way an already-uploaded field's carried-over value is.
        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->resume($first->submission)
                ->submit(
                    data: ['email' => 'test@example.com'],
                    files: ['avatar' => [UploadedFile::fake()->create('virus.php', 10)]],
                );

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('avatar.0', $e->errors());
        }

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_still_validates_min_files_when_removing_files_without_uploading_a_new_one()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [
                            ['handle' => 'email', 'field' => ['type' => 'email']],
                            ['handle' => 'gallery', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'min_files' => 2]],
                        ]],
                    ],
                ],
            ],
        ]))->save();

        $first = app(SubmitForm::class)
            ->form($form)
            ->page('main')
            ->submit(
                data: ['email' => 'test@example.com'],
                files: ['gallery' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')]],
            );

        // Dropping one of the two files, without uploading a replacement, still leaves an
        // array-shaped value. It should keep being validated against min_files as normal.
        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->resume($first->submission)
                ->submit(data: ['email' => 'test@example.com', 'gallery' => [$first->submission->get('gallery')[0]]]);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('gallery', $e->errors());
        }

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_rejects_an_upload_value_that_was_never_actually_uploaded()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [
                            ['handle' => 'email', 'field' => ['type' => 'email']],
                            ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                        ]],
                    ],
                ],
            ],
        ]))->save();

        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->submit(data: ['email' => 'test@example.com', 'avatar' => '../../framework/sessions/x']);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('avatar', $e->errors());
        }

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_rejects_a_forged_multi_file_upload_value()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [
                            ['handle' => 'email', 'field' => ['type' => 'email']],
                            ['handle' => 'gallery', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                        ]],
                    ],
                ],
            ],
        ]))->save();

        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->submit(data: ['email' => 'test@example.com', 'gallery' => ['../../framework/sessions/x']]);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('gallery', $e->errors());
        }

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_rejects_a_resubmitted_upload_value_that_doesnt_match_whats_stored()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('uploads')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [
                            ['handle' => 'email', 'field' => ['type' => 'email']],
                            ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                        ]],
                    ],
                ],
            ],
        ]))->save();

        $first = app(SubmitForm::class)
            ->form($form)
            ->page('main')
            ->submit(
                data: ['email' => 'test@example.com'],
                files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
            );

        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->resume($first->submission)
                ->submit(data: ['email' => 'test@example.com', 'avatar' => '../../framework/sessions/x']);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('avatar', $e->errors());
        }

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_validates_the_extension_of_uploaded_files()
    {
        Bus::fake(); // Otherwise the temp file is deleted by DeleteTemporaryFiles right after submission.
        Storage::fake('local');

        // store: false makes this a temporary "files" upload rather than a stored asset.
        $form = tap(Form::make('uploads')->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [['handle' => 'document', 'field' => ['type' => 'upload', 'store' => false]]]],
                    ],
                ],
            ],
        ]))->save();

        // A disallowed extension is rejected.
        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->submit(data: [], files: ['document' => [UploadedFile::fake()->create('virus.php', 10)]]);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('document.0', $e->errors());
        }

        // An allowed extension passes.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('main')
            ->submit(data: [], files: ['document' => [UploadedFile::fake()->create('resume.pdf', 10)]]);

        $this->assertTrue($result->isFinalized());
        Storage::disk('local')->assertExists('statamic/form-uploads/'.$result->submission->get('document')[0]);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_removes_uploaded_assets_on_silent_failure()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = $this->uploadForm(honeypot: true);

        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->submit(
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

        $form = $this->uploadForm();

        Event::listen(FormSubmitted::class, fn () => false);

        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->submit(
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

        $form = $this->uploadForm();

        Event::listen(FormSubmitted::class, function () {
            throw ValidationException::withMessages(['custom' => 'Error']);
        });

        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('main')
                ->submit(
                    data: ['email' => 'test@example.com'],
                    files: ['avatar' => [UploadedFile::fake()->image('avatar.jpg')]],
                );
        } catch (ValidationException $e) {
            // Expected
        }

        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }

    #[Test]
    public function it_returns_the_next_page_and_saves_a_partial_submission_when_submitting_a_non_final_page()
    {
        Bus::fake();
        Event::fake([FormSubmitted::class, SubmissionCreated::class, SubmissionFinalized::class]);

        $form = $this->multiPageForm();

        $result = app(SubmitForm::class)
            ->form($form)
            ->page('one')
            ->submit(['name' => 'Olaf']);

        $this->assertInstanceOf(SubmissionResult::class, $result);
        $this->assertEquals('two', $result->nextPage);
        $this->assertFalse($result->isFinalized());

        $this->assertCount(1, $form->submissions());
        $this->assertTrue($result->submission->isPartial());
        $this->assertEquals('Olaf', $result->submission->get('name'));

        Event::assertDispatched(SubmissionCreated::class);
        Event::assertNotDispatched(FormSubmitted::class);
        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertNotDispatched(SendEmails::class);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_takes_page_logic_into_account_when_resolving_the_next_page()
    {
        $form = $this->multiPageFormWithLogic();

        // A matching submission follows the rule past page two, straight to page three.
        $jumped = app(SubmitForm::class)->form($form)->page('one')->submit(['name' => 'skip']);
        $this->assertEquals('three', $jumped->nextPage);

        // A non-matching submission advances to the next sequential page.
        $advanced = app(SubmitForm::class)->form($form)->page('one')->submit(['name' => 'Olaf']);
        $this->assertEquals('two', $advanced->nextPage);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_scopes_stored_values_to_the_current_page()
    {
        $form = $this->multiPageForm();

        // The email belongs to a later page, so it shouldn't be stored when submitting page one.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('one')
            ->submit(['name' => 'Olaf', 'email' => 'olaf@example.com']);

        $this->assertEquals('Olaf', $result->submission->get('name'));
        $this->assertNull($result->submission->get('email'));

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_scopes_stored_values_to_the_current_page_when_the_page_imports_a_fieldset()
    {
        $fieldset = Fieldset::make('address')->setContents([
            'fields' => [
                ['handle' => 'city', 'field' => ['type' => 'text']],
            ],
        ]);
        Fieldset::shouldReceive('find')->with('address')->andReturn($fieldset);

        $form = tap(Form::make('signup')->formFields([
            'pages' => [
                [
                    'id' => 'one',
                    'sections' => [['fields' => [['handle' => 'name', 'field' => ['type' => 'short_answer']]]]],
                ],
                [
                    'id' => 'two',
                    'sections' => [['fields' => [['import' => 'address', 'prefix' => 'shipping_']]]],
                ],
                [
                    'id' => 'three',
                    'sections' => [['fields' => [['handle' => 'message', 'field' => ['type' => 'long_answer']]]]],
                ],
            ],
        ]))->save();

        $result = app(SubmitForm::class)
            ->form($form)
            ->page('two')
            ->submit(['shipping_city' => 'Glasgow', 'message' => 'Hello']);

        $this->assertEquals('three', $result->nextPage);
        $this->assertEquals('Glasgow', $result->submission->get('shipping_city'));
        $this->assertNull($result->submission->get('message'));

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_only_validates_the_current_pages_fields()
    {
        $form = $this->multiPageForm();

        // Page one has no required fields, so the email being required on page two
        // shouldn't cause a validation failure when submitting page one.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('one')
            ->submit(['name' => 'Olaf']);

        $this->assertEquals('two', $result->nextPage);

        // Page two requires the email.
        try {
            app(SubmitForm::class)
                ->form($form)
                ->page('two')
                ->resume($result->submission)
                ->submit([]);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_only_runs_the_honeypot_check_on_the_final_page()
    {
        $form = $this->multiPageForm();

        // A filled honeypot on non-final pages is ignored; the partial submission saves normally.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('one')
            ->submit(['name' => 'Olaf', 'winnie' => 'the pooh']);

        $this->assertEquals('two', $result->nextPage);
        $this->assertTrue($result->submission->isPartial());

        $result = app(SubmitForm::class)
            ->form($form)
            ->page('two')
            ->resume($result->submission)
            ->submit(['email' => 'olaf@example.com']);

        // On the final page the honeypot triggers a silent failure.
        $action = app(SubmitForm::class)
            ->form($form)
            ->page('three')
            ->resume($result->submission);

        try {
            $action->submit(['message' => 'Hello', 'winnie' => 'the pooh']);

            $this->fail('Expected SilentFormFailureException was not thrown');
        } catch (SilentFormFailureException $e) {
            $this->assertNotNull($action->submission());
        }

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_only_dispatches_the_form_submitted_event_on_the_final_page()
    {
        $form = $this->multiPageForm();

        Event::listen(FormSubmitted::class, fn () => false);

        $result = app(SubmitForm::class)
            ->form($form)
            ->page('one')
            ->submit(['name' => 'Olaf']);

        $this->assertTrue($result->submission->isPartial());
        $this->assertEquals('Olaf', $result->submission->get('name'));

        $result = app(SubmitForm::class)
            ->form($form)
            ->page('two')
            ->resume($result->submission)
            ->submit(['email' => 'olaf@example.com']);

        $action = app(SubmitForm::class)
            ->form($form)
            ->page('three')
            ->resume($result->submission);

        try {
            $action->submit(['message' => 'Hello']);

            $this->fail('Expected SilentFormFailureException was not thrown');
        } catch (SilentFormFailureException $e) {
            $this->assertNotNull($action->submission());
        }

        // The submission stays partial since completion was silently aborted.
        $this->assertTrue($form->submission($result->submission->id())->isPartial());

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_resumes_a_partial_submission_on_a_later_page()
    {
        Bus::fake();
        Event::fake([FormSubmitted::class, SubmissionFinalized::class]);

        $form = $this->multiPageForm();

        $first = app(SubmitForm::class)
            ->form($form)
            ->page('one')
            ->submit(['name' => 'Olaf']);

        // Resuming continues the same partial submission on the next page rather than starting over.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('two')
            ->resume($first->submission)
            ->submit(['email' => 'olaf@example.com']);

        $this->assertEquals($first->submission->id(), $result->submission->id());
        $this->assertCount(1, $form->submissions());
        $this->assertEquals('three', $result->nextPage);

        $this->assertFalse($result->isFinalized());
        $this->assertTrue($result->submission->isPartial());
        Event::assertNotDispatched(FormSubmitted::class);
        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertNotDispatched(SendEmails::class);

        // Earlier-page values are preserved while the new page's values are merged in.
        $stored = $form->submission($result->submission->id());
        $this->assertEquals('Olaf', $stored->get('name'));
        $this->assertEquals('olaf@example.com', $stored->get('email'));

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_finalizes_the_partial_submission_on_the_final_page()
    {
        Bus::fake();

        $form = $this->multiPageForm();

        // An in-progress partial submission that has already collected the earlier pages' values.
        $partial = tap($form->makeSubmission()->data(['name' => 'Olaf', 'email' => 'olaf@example.com'])->asPartial())->save();

        // Faked after seeding so the seeding's created event is out of scope.
        Event::fake([SubmissionCreated::class, SubmissionFinalized::class]);

        $result = app(SubmitForm::class)
            ->form($form)
            ->page('three')
            ->resume($partial)
            ->submit(['message' => 'Hello']);

        // The partial submission is promoted to a finalized one rather than a new one being created.
        $this->assertEquals($partial->id(), $result->submission->id());
        $this->assertCount(1, $form->submissions());
        $this->assertNull($result->nextPage);
        $this->assertTrue($result->isFinalized());

        $stored = $form->submission($result->submission->id());
        $this->assertFalse($stored->isPartial());

        // Earlier pages' values are preserved while the final page's values are merged in.
        $this->assertEquals('Olaf', $stored->get('name'));
        $this->assertEquals('olaf@example.com', $stored->get('email'));
        $this->assertEquals('Hello', $stored->get('message'));

        // Finalizing fires the completion events once; it doesn't re-create the submission.
        Event::assertNotDispatched(SubmissionCreated::class);
        Event::assertDispatched(SubmissionFinalized::class, 1);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_returns_to_the_first_page_when_finalizing_without_completing_every_page()
    {
        Bus::fake();
        Event::fake([FormSubmitted::class, SubmissionFinalized::class]);

        $form = $this->multiPageForm();

        // Jump straight to the final page without completing the earlier pages.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('three')
            ->submit(['message' => 'Hello']);

        // Rather than finalizing, the user is sent back to the first page to fill the form in properly.
        $this->assertEquals('one', $result->nextPage);
        $this->assertFalse($result->isFinalized());
        $this->assertTrue($result->submission->isPartial());

        Event::assertNotDispatched(FormSubmitted::class);
        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertNotDispatched(SendEmails::class);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_finalizes_when_page_logic_legitimately_skips_a_page()
    {
        $form = $this->multiPageFormWithLogic();

        // name=skip satisfies page one's rule, routing straight to page three and skipping page two.
        $first = app(SubmitForm::class)->form($form)->page('one')->submit(['name' => 'skip']);
        $this->assertEquals('three', $first->nextPage);
        $this->assertFalse($first->isFinalized());

        // Completing page three finalizes, because every page on the path actually taken is done.
        $result = app(SubmitForm::class)
            ->form($form)
            ->page('three')
            ->resume($first->submission)
            ->submit(['message' => 'Hello']);

        $this->assertNull($result->nextPage);
        $this->assertTrue($result->isFinalized());

        $form->submissions()->each->delete();
    }

    private function uploadForm(bool $honeypot = false)
    {
        $form = Form::make('uploads');

        if ($honeypot) {
            $form->honeypot('winnie');
        }

        return tap($form->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'email', 'field' => ['type' => 'email']],
                                ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                            ],
                        ],
                    ],
                ],
            ],
        ]), fn ($f) => $f->save());
    }
}
