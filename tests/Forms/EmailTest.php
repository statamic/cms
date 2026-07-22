<?php

namespace Tests\Forms;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Forms\Email;
use Statamic\Forms\Submission;
use Statamic\Forms\Uploaders\FormFileUpload;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_recipient_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['to' => $address]);

        $this->assertEquals($expected, $email->to);
    }

    #[Test]
    #[DataProvider('singleAddressProvider')]
    public function it_adds_sender_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['from' => $address]);

        $this->assertEquals($expected, $email->from);
    }

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_reply_to_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['reply_to' => $address]);

        $this->assertEquals($expected, $email->replyTo);
    }

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_cc_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['cc' => $address]);

        $this->assertEquals($expected, $email->cc);
    }

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_bcc_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['bcc' => $address]);

        $this->assertEquals($expected, $email->bcc);
    }

    public static function singleAddressProvider()
    {
        return [
            'single email' => ['foo@bar.com', [
                ['address' => 'foo@bar.com', 'name' => null],
            ]],
            'single email with name' => ['Foo Bar <foo@bar.com>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
            ]],
            'single email using antlers' => ['{{ email }}', [
                ['address' => 'foo@bar.com', 'name' => null],
            ]],
            'single email with name using antlers' => ['{{ name }} <{{ email }}>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
            ]],
            'single email from global set using antlers' => ['{{ company_information:email }}', [
                ['address' => 'info@example.com', 'name' => null],
            ]],
            'single email with name from global set using antlers' => ['{{ company_information:name }} <{{ company_information:email }}>', [
                ['address' => 'info@example.com', 'name' => 'Example Company'],
            ]],
        ];
    }

    public static function multipleAddressProvider()
    {
        return array_merge(static::singleAddressProvider(), [
            'multiple emails' => ['foo@bar.com, baz@qux.com', [
                ['address' => 'foo@bar.com', 'name' => null],
                ['address' => 'baz@qux.com', 'name' => null],
            ]],
            'multiple emails with name' => ['Foo Bar <foo@bar.com>, Baz Qux <baz@qux.com>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
                ['address' => 'baz@qux.com', 'name' => 'Baz Qux'],
            ]],
            'multiple emails using antlers' => ['{{ email }}, baz@qux.com', [
                ['address' => 'foo@bar.com', 'name' => null],
                ['address' => 'baz@qux.com', 'name' => null],
            ]],
            'multiple emails with name using antlers' => ['{{ name }} <{{ email }}>, Baz Qux <baz@qux.com>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
                ['address' => 'baz@qux.com', 'name' => 'Baz Qux'],
            ]],
        ]);
    }

    #[Test]
    public function it_adds_subject_from_the_config()
    {
        $email = $this->makeEmailWithConfig(['subject' => 'A nice form subject']);

        $this->assertEquals('A nice form subject', $email->subject);
    }

    #[Test]
    public function it_adds_data_to_the_view()
    {
        $socialBlueprint = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        $companyBlueprint = Blueprint::makeFromFields(['company_name' => ['type' => 'text']])->setHandle('company')->setNamespace('globals');

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($socialBlueprint);
        BlueprintRepository::shouldReceive('find')->with('globals.company')->andReturn($companyBlueprint);

        $social = tap(GlobalSet::make('social'))->save();
        $social->inDefaultSite()->data(['twitter' => '@statamic'])->save();
        $company = tap(GlobalSet::make('company'))->save();
        $company->inDefaultSite()->data(['company_name' => 'Statamic'])->save();

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'foo', 'field' => ['type' => 'short_answer']],
            ],
        ]))->save();
        $submission = $form->makeSubmission()->data(['foo' => 'bar']);

        $email = $this->makeEmailWithSubmission($submission);

        $this->assertEquals(collect([
            // from the submission
            'id',
            'foo',
            'form',

            // globals
            'company',
            'social',

            // manual "system" vars added to email
            'form_config',
            'email_config',
            'config',
            'date',
            'fields',
            'locale',
            'now',
            'site',
            'site_url',
            'today',
        ])->sort()->values()->all(), collect($email->viewData)->sortKeys()->keys()->all());

        $this->assertEquals('bar', $email->viewData['foo']);
        $this->assertEquals($submission->id(), $email->viewData['id']);
        $this->assertEquals($form, $email->viewData['form']->value());
        $this->assertEquals('Statamic', (string) $email->viewData['company']['company_name']);
    }

    #[Test]
    public function it_excludes_informational_and_structural_fields_from_the_fields_data()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                ['handle' => 'intro', 'field' => ['type' => 'heading']],
                ['handle' => 'gap', 'field' => ['type' => 'spacer']],
                ['handle' => 'blurb', 'field' => ['type' => 'paragraph']],
            ],
        ]))->save();

        $submission = $form->makeSubmission()->data(['name' => 'Foo Bar']);

        $email = tap(new Email($submission, ['to' => 'test@test.com'], Site::default()))->build();

        $this->assertEquals(['name'], collect($email->viewData['fields'])->pluck('handle')->all());
    }

    #[Test]
    public function it_escapes_submitted_values_in_the_automagic_email()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                ['handle' => 'message', 'field' => ['type' => 'long_answer']],

                // The select/radio/checkboxes branches emit `label ?? value`, and the label
                // falls back to the raw value when there's no matching option. The raw value
                // is attacker-controlled, so it must be escaped. The option label is author
                // controlled (it lives in the form), so it's not really exploitable, but
                // we escape it too for consistency. Both situations are asserted below.
                ['handle' => 'select_labelled', 'field' => ['type' => 'dropdown', 'options' => ['a' => '<script>select-label</script>']]],
                ['handle' => 'select_raw', 'field' => ['type' => 'dropdown']],
                ['handle' => 'radio_labelled', 'field' => ['type' => 'multi_choice', 'options' => ['b' => '<script>radio-label</script>']]],
                ['handle' => 'radio_raw', 'field' => ['type' => 'multi_choice']],
                ['handle' => 'checkboxes', 'field' => ['type' => 'checkboxes', 'options' => ['c' => '<script>checkbox-label</script>']]],
            ],
        ]))->save();

        $submission = $form->makeSubmission()->data([
            'name' => '<img src=x onerror=alert(1)>',
            'message' => "line one\n<script>alert(2)</script>",
            'select_labelled' => 'a',
            'select_raw' => '"><svg onload=alert(3)>',
            'radio_labelled' => 'b',
            'radio_raw' => '"><svg onload=alert(4)>',
            'checkboxes' => ['c', '<b>raw-checkbox</b>'],
        ]);

        $email = new Email($submission, ['to' => 'test@test.com'], Site::default());

        $body = $email->render();

        // Attacker-controlled values are escaped, not emitted as live markup.
        $assertEscaped = function ($raw, $escaped) use ($body) {
            $this->assertStringNotContainsString($raw, $body);
            $this->assertStringContainsString($escaped, $body);
        };

        // text / textarea
        $assertEscaped('<img src=x onerror=alert(1)>', '&lt;img src=x onerror=alert(1)&gt;');
        $assertEscaped('<script>alert(2)</script>', '&lt;script&gt;alert(2)&lt;/script&gt;');

        // select — the author-controlled option label (sanitized for consistency) and
        // the attacker-controlled raw fallback value.
        $assertEscaped('<script>select-label</script>', '&lt;script&gt;select-label&lt;/script&gt;');
        $assertEscaped('<svg onload=alert(3)>', '&lt;svg onload=alert(3)&gt;');

        // radio — same, option label and raw fallback value.
        $assertEscaped('<script>radio-label</script>', '&lt;script&gt;radio-label&lt;/script&gt;');
        $assertEscaped('<svg onload=alert(4)>', '&lt;svg onload=alert(4)&gt;');

        // checkboxes — same, option label and raw fallback value.
        $assertEscaped('<script>checkbox-label</script>', '&lt;script&gt;checkbox-label&lt;/script&gt;');
        $assertEscaped('<b>raw-checkbox</b>', '&lt;b&gt;raw-checkbox&lt;/b&gt;');

        // Legitimate multiline text still renders line breaks.
        $this->assertStringContainsString('line one<br', $body);
    }

    #[Test]
    public function attachments_are_added()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        // "store: true" means that the uploaded file is now an asset.
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
            ],
        ]))->save();

        tap(Asset::make()->container('avatars')->path('avatar.jpg'))->save();

        $submission = $form->makeSubmission()->data(['avatar' => 'avatar.jpg']);

        $email = tap(new Email($submission, ['to' => 'test@test.com', 'attachments' => true], Site::default()))->build();

        $this->assertTrue($email->hasAttachmentFromStorageDisk('avatars', 'avatar.jpg'));
    }

    #[Test]
    public function it_attaches_temporary_file_upload()
    {
        Storage::fake('local');

        // "store: false" means that the uploaded file is temporary.
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'upload', 'store' => false, 'max_files' => 1]],
            ],
        ]))->save();

        $submission = $form->makeSubmission();
        $path = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);
        $submission->data(['document' => $path]);

        $email = tap(new Email($submission, ['to' => 'test@test.com', 'attachments' => true], Site::default()))->build();

        $this->assertTrue($email->hasAttachmentFromStorageDisk('local', 'statamic/form-uploads/'.$path));
    }

    #[Test]
    public function it_attaches_temporary_file_upload_from_the_configured_disk_and_path()
    {
        config([
            'statamic.system.file_uploads_disk' => 'uploads',
            'statamic.forms.file_uploads_path' => 'temp-form-uploads',
        ]);

        Storage::fake('local');
        Storage::fake('uploads');

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'upload', 'store' => false, 'max_files' => 1]],
            ],
        ]))->save();

        $submission = $form->makeSubmission();
        $path = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);
        $submission->data(['document' => $path]);

        $email = tap(new Email($submission, ['to' => 'test@test.com', 'attachments' => true], Site::default()))->build();

        $this->assertTrue($email->hasAttachmentFromStorageDisk('uploads', 'temp-form-uploads/'.$path));
    }

    #[Test]
    public function it_attaches_files_from_assets_field()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'avatar', 'field' => ['type' => 'assets', 'container' => 'avatars', 'max_files' => 1]],
            ],
        ]))->save();

        tap(Asset::make()->container('avatars')->path('avatar.jpg'))->save();

        $submission = $form->makeSubmission()->data(['avatar' => 'avatar.jpg']);

        $email = tap(new Email($submission, ['to' => 'test@test.com', 'attachments' => true], Site::default()))->build();

        $this->assertTrue($email->hasAttachmentFromStorageDisk('avatars', 'avatar.jpg'));
    }

    #[Test]
    public function it_attaches_files_from_files_field()
    {
        Storage::fake('local');

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'files', 'max_files' => 1]],
            ],
        ]))->save();

        $documentPath = now()->timestamp.'/resume.pdf';
        Storage::disk('local')->put('statamic/file-uploads/'.$documentPath, 'contents');

        $submission = $form->makeSubmission()->data(['document' => $documentPath]);

        $email = tap(new Email($submission, ['to' => 'test@test.com', 'attachments' => true], Site::default()))->build();

        $this->assertTrue($email->hasAttachmentFromStorageDisk('local', 'statamic/file-uploads/'.$documentPath));
    }

    #[Test]
    public function it_attaches_files_from_files_field_on_the_configured_disk_and_path()
    {
        config([
            'statamic.system.file_uploads_disk' => 'uploads',
            'statamic.system.file_uploads_path' => 'temp-uploads',
        ]);

        Storage::fake('local');
        $uploadsDisk = Storage::fake('uploads');

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'files', 'max_files' => 1]],
            ],
        ]))->save();

        $documentPath = now()->timestamp.'/resume.pdf';
        $uploadsDisk->put('temp-uploads/'.$documentPath, 'contents');

        $submission = $form->makeSubmission()->data(['document' => $documentPath]);

        $email = tap(new Email($submission, ['to' => 'test@test.com', 'attachments' => true], Site::default()))->build();

        $this->assertTrue($email->hasAttachmentFromStorageDisk('uploads', 'temp-uploads/'.$documentPath));
    }

    #[Test]
    public function it_adds_renderable_fields()
    {
        $this->markTestIncomplete();
    }

    private function makeEmailWithSubmission(Submission $submission)
    {
        return tap(new Email($submission, ['to' => 'test@test.com'], Site::default()))->build();
    }

    private function makeEmailWithConfig(array $config)
    {
        $globalSet = GlobalSet::make()->handle('company_information')->save();
        $globalSet->in('en')->data([
            'name' => 'Example Company',
            'email' => 'info@example.com',
        ])->save();

        $companyInformationBlueprint = Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
            'email' => ['type' => 'text'],
        ]);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('globals.company_information')->andReturn($companyInformationBlueprint);

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                ['handle' => 'email', 'field' => ['type' => 'email']],
            ],
        ]))->save();

        $submission = $form->makeSubmission()->data([
            'name' => 'Foo Bar',
            'email' => 'foo@bar.com',
        ]);

        return tap(new Email(
            $submission,
            array_merge(['to' => 'test@test.com'], $config),
            Site::default()
        ))->build();
    }

    #[Test]
    public function the_sites_locale_gets_used_on_the_mailable()
    {
        $this->setSites([
            'one' => ['locale' => 'en_US', 'url' => '/one'],
            'two' => ['locale' => 'fr_Fr', 'url' => '/two'],
            'three' => ['locale' => 'de_CH', 'lang' => 'de_CH', 'url' => '/three'],
        ]);

        $makeEmail = function ($site) {
            $submission = Mockery::mock(Submission::class);

            return new Email($submission, ['to' => 'test@test.com'], $site);
        };

        $this->assertEquals('en', $makeEmail(Site::get('one'))->locale);
        $this->assertEquals('fr', $makeEmail(Site::get('two'))->locale);
        $this->assertEquals('de_CH', $makeEmail(Site::get('three'))->locale);
    }
}
