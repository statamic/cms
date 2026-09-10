<?php

namespace Tests\Forms\Connections;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormConnection;
use Statamic\Facades\User;
use Statamic\Forms\Connections\Email;
use Statamic\Forms\SendEmails;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EmailConnectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
        $app['config']['mail.from'] = ['address' => 'default@example.com', 'name' => 'Default Sender'];
    }

    #[Test]
    public function it_returns_a_job_that_sends_the_emails()
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', 'to' => ['first@example.com']],
            ['id' => 'two', 'to' => ['second@example.com']],
        ]]))->save();

        $this->assertInstanceOf(
            SendEmails::class,
            (new Email)->setConfig($form->connections()->get('email'))->finalized($form->makeSubmission())
        );
    }

    #[Test]
    #[DataProvider('emailCountProvider')]
    public function it_counts_the_configured_emails(array $emails, int $count)
    {
        $form = tap(Form::make('test')->connections(['email' => $emails]))->save();

        $this->assertEquals($count, (new Email)->count($form));
    }

    public static function emailCountProvider(): array
    {
        return [
            'none configured' => [[], 0],
            'one configured' => [[['id' => 'one', 'to' => ['first@example.com']]], 1],
            'two configured' => [[['id' => 'one', 'to' => ['first@example.com']], ['id' => 'two', 'to' => ['second@example.com']]], 2],
        ];
    }

    #[Test]
    public function it_renders_the_vue_component()
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', 'to' => ['first@example.com'], 'subject' => 'First'],
            ['id' => 'two', 'to' => ['second@example.com'], 'subject' => 'Second'],
        ]]))->save();

        $component = (new Email)->render($form)->toArray();

        $this->assertEquals('email-connection', $component['name']);
        $this->assertEquals(['blueprint', 'meta', 'defaults', 'previewUrl'], array_keys($component['props']));
        $this->assertEquals(['one', 'two'], array_keys($component['props']['meta']));
        $this->assertEquals([], $component['props']['defaults']['values']['to']);
        $this->assertArrayHasKey('meta', $component['props']['defaults']);
        $this->assertEquals(cp_route('forms.connect.email.preview', 'test'), $component['props']['previewUrl']);
    }

    #[Test]
    public function it_registers_a_preview_route()
    {
        FormConnection::routes();

        $route = collect(Route::getRoutes())->first(fn ($route) => $route->getName() === 'forms.connect.email.preview');

        $this->assertNotNull($route);
        $this->assertEquals('forms/{form}/connect/email/preview', $route->uri());
        $this->assertContains('POST', $route->methods());
        $this->assertContains('can:edit,form', $route->middleware());
    }

    #[Test]
    public function it_previews_an_email_using_a_sample_submission()
    {
        $form = $this->makeForm();

        $response = $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email', 'Team <team@example.com>'],
                'from' => ['sender@example.com'],
                'reply_to' => ['field:email'],
                'subject' => 'Hello {{ name }}',
            ])
            ->assertOk()
            ->assertJson([
                'from' => ['sender@example.com'],
                'format' => 'html',
                'cc' => [],
                'bcc' => [],
                'sample' => true,
            ]);

        $this->assertStringStartsWith('Hello ', $response->json('subject'));
        $this->assertNotEquals('Hello ', $response->json('subject'));
        $this->assertNotFalse(filter_var($response->json('to.0'), FILTER_VALIDATE_EMAIL));
        $this->assertEquals('Team <team@example.com>', $response->json('to.1'));
        $this->assertEquals($response->json('to.0'), $response->json('reply_to.0'));
        $this->assertStringContainsString('<b>Name:</b>', $response->json('body'));
        $this->assertStringContainsString('<b>Email:</b>', $response->json('body'));
        $this->assertEquals(0, $form->querySubmissions()->count());
    }

    #[Test]
    public function it_previews_using_the_latest_submission_when_the_user_can_view_submissions()
    {
        $form = $this->makeForm();
        $this->makeSubmission($form, ['name' => 'Older', 'email' => 'older@example.com'], '2026-01-01 10:00');
        $this->makeSubmission($form, ['name' => 'Latest', 'email' => 'latest@example.com'], '2026-02-01 10:00');
        $this->makeSubmission($form, ['name' => 'Partial', 'email' => 'partial@example.com'], '2026-03-01 10:00', partial: true);

        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email'],
                'subject' => 'Hello {{ name }}',
            ])
            ->assertOk()
            ->assertJson([
                'subject' => 'Hello Latest',
                'to' => ['latest@example.com'],
                'sample' => false,
            ]);
    }

    #[Test]
    public function it_previews_using_sample_data_when_the_user_cannot_view_submissions()
    {
        $form = $this->makeForm();
        $this->makeSubmission($form, ['name' => 'Latest', 'email' => 'latest@example.com'], '2026-02-01 10:00');

        $response = $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email'],
                'subject' => 'Hello {{ name }}',
            ])
            ->assertOk()
            ->assertJson(['sample' => true]);

        $this->assertNotEquals('Hello Latest', $response->json('subject'));
        $this->assertNotEquals(['latest@example.com'], $response->json('to'));
    }

    #[Test]
    public function it_previews_with_the_default_sender_when_none_is_configured()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
            ])
            ->assertOk()
            ->assertJson(['from' => ['Default Sender <default@example.com>']]);
    }

    #[Test]
    public function it_previews_a_custom_html_view()
    {
        $form = $this->makeForm();

        $response = $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
                'html' => 'emails.preview',
            ])
            ->assertOk()
            ->assertJson(['format' => 'html']);

        $this->assertStringStartsWith('<h1>Hello ', trim($response->json('body')));
    }

    #[Test]
    public function it_previews_a_markdown_view()
    {
        $form = $this->makeForm();

        $response = $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
                'html' => 'emails.preview-markdown',
                'markdown' => true,
            ])
            ->assertOk()
            ->assertJson(['format' => 'html']);

        $this->assertMatchesRegularExpression('/<h1[^>]*>Hello \S+/', $response->json('body'));
    }

    #[Test]
    public function it_previews_a_text_only_view_as_text()
    {
        $form = $this->makeForm();

        $response = $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
                'text' => 'emails.preview-text',
            ])
            ->assertOk()
            ->assertJson(['format' => 'text']);

        $this->assertStringStartsWith('Hello ', trim($response->json('body')));
    }

    #[Test]
    #[DataProvider('previewAttachmentsProvider')]
    public function it_previews_whether_files_will_be_attached(array $fields, bool $enabled, bool $expected)
    {
        $form = $this->makeForm($fields);

        $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
                'attachments' => $enabled,
            ])
            ->assertOk()
            ->assertJson(['attachments' => $expected]);
    }

    public static function previewAttachmentsProvider(): array
    {
        $upload = [['handle' => 'cv', 'field' => ['type' => 'upload']]];

        return [
            'enabled with an upload field' => [$upload, true, true],
            'disabled with an upload field' => [$upload, false, false],
            'enabled without an upload field' => [[], true, false],
        ];
    }

    #[Test]
    public function it_returns_the_error_when_the_preview_cannot_be_rendered()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithEditPermission())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
                'html' => 'emails.missing',
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'View [emails.missing] not found.']);
    }

    #[Test]
    public function it_denies_the_preview_if_you_dont_have_permission()
    {
        $form = $this->makeForm();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('forms.connect.email.preview', $form->handle()), ['id' => 'abc'])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_pre_processes_email_configs()
    {
        $form = tap(Form::make('test')->connections(['email' => [
            [
                'id' => 'one',
                'to' => ['first@example.com'],
                'subject' => 'First',
                'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and']],
            ],
            ['id' => 'two', 'to' => ['second@example.com'], 'subject' => 'Second', 'enabled' => false],
        ]]))->save();

        $configs = (new Email)->preProcess($form->connections()->get('email'), $form);

        $this->assertEquals('one', $configs[0]['id']);
        $this->assertTrue($configs[0]['enabled']);
        $this->assertEquals(['first@example.com'], $configs[0]['to']);
        $this->assertEquals('First', $configs[0]['subject']);
        $this->assertNotEmpty($configs[0]['conditions'][0]['_id']);
        $this->assertEquals('name', $configs[0]['conditions'][0]['field']);
        $this->assertEquals('Bob', $configs[0]['conditions'][0]['value']);
        $this->assertEquals('two', $configs[1]['id']);
        $this->assertFalse($configs[1]['enabled']);
        $this->assertEquals(['second@example.com'], $configs[1]['to']);
        $this->assertEquals('Second', $configs[1]['subject']);
        $this->assertEquals([], $configs[1]['conditions']);
    }

    #[Test]
    #[DataProvider('legacyAddressProvider')]
    public function it_converts_legacy_address_strings_into_arrays(string $handle)
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', $handle => 'first@example.com, second@example.com'],
        ]]))->save();

        $configs = (new Email)->preProcess($form->connections()->get('email'), $form);

        $this->assertEquals(['first@example.com', 'second@example.com'], $configs[0][$handle]);
    }

    public static function legacyAddressProvider(): array
    {
        return [
            'to' => ['to'],
            'cc' => ['cc'],
            'bcc' => ['bcc'],
            'reply_to' => ['reply_to'],
        ];
    }

    #[Test]
    public function it_validates_email_configs()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'email']],
            ],
        ]))->save();

        $validator = Validator::make([
            [
                'id' => 'abc',
                'to' => ['foo@example.com', 'field:email'],
                'cc' => ['cc@example.com'],
                'enabled' => true,
                'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and']],
            ],
        ], (new Email)->rules($form));

        $this->assertTrue($validator->passes());
    }

    #[Test]
    #[DataProvider('invalidConfigs')]
    public function it_rejects_invalid_configs($configs, $errors)
    {
        $form = tap(Form::make('test'))->save();

        $validator = Validator::make($configs, (new Email)->rules($form));

        $this->assertTrue($validator->fails());

        foreach ($errors as $key) {
            $this->assertTrue($validator->errors()->has($key));
        }
    }

    public static function invalidConfigs(): array
    {
        return [
            'config not an array' => [['nope'], ['0']],
            'config without a recipient' => [[['from' => 'foo@example.com']], ['0.to']],
            'config with an empty recipient list' => [[['to' => []]], ['0.to']],
            'config with an invalid recipient' => [[['to' => ['not-an-email']]], ['0.to']],
            'config with an invalid legacy string recipient' => [[['to' => 'foo@example.com, not-an-email']], ['0.to']],
            'config with an unknown field reference' => [[['to' => ['field:unknown']]], ['0.to']],
            'config with an invalid cc' => [[['to' => ['foo@example.com'], 'cc' => ['not-an-email']]], ['0.cc']],
            'config with an invalid sender' => [[['to' => ['foo@example.com'], 'from' => 'not-an-email']], ['0.from']],
            'config with a non-boolean enabled' => [[['to' => ['foo@example.com'], 'enabled' => 'nope']], ['0.enabled']],
            'config with non-array conditions' => [[['to' => ['foo@example.com'], 'conditions' => 'nope']], ['0.conditions']],
            'config with a non-array condition' => [[['to' => ['foo@example.com'], 'conditions' => ['nope']]], ['0.conditions.0']],
        ];
    }

    #[Test]
    public function it_processes_email_configs()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'email']],
            ],
        ]))->save();

        $this->assertEquals([
            ['id' => 'abc', 'to' => ['new@example.com', 'field:email'], 'subject' => 'Updated'],
            ['id' => 'def', 'to' => ['another@example.com'], 'enabled' => false, 'conditions' => [
                ['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
            ]],
            ['id' => 'ghi', 'to' => ['third@example.com'], 'markdown' => true, 'attachments' => true],
        ], (new Email)->process([
            ['id' => 'abc', 'to' => ['new@example.com', 'field:email'], 'subject' => 'Updated', 'enabled' => true, 'markdown' => false, 'attachments' => false],
            ['id' => 'def', 'to' => ['another@example.com'], 'enabled' => false, 'conditions' => [
                ['_id' => 'vue-row', 'field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
            ]],
            ['id' => 'ghi', 'to' => ['third@example.com'], 'markdown' => true, 'attachments' => true],
        ], $form));
    }

    #[Test]
    public function it_doesnt_persist_the_client_row_state()
    {
        $form = tap(Form::make('test'))->save();

        $this->assertEquals([
            ['id' => 'abc', 'to' => ['foo@example.com']],
        ], (new Email)->process([
            ['_id' => 'vue-row', 'id' => 'abc', 'to' => ['foo@example.com']],
        ], $form));
    }

    #[Test]
    public function it_generates_an_id_for_configs_that_dont_have_one()
    {
        $form = tap(Form::make('test'))->save();

        $config = (new Email)->process([['to' => ['foo@example.com']]], $form)[0];

        $this->assertNotEmpty($config['id']);
        $this->assertEquals(['foo@example.com'], $config['to']);
    }

    #[Test]
    public function it_removes_null_values_from_configs()
    {
        $form = tap(Form::make('test'))->save();

        $this->assertEquals([[
            'id' => 'abc',
            'to' => ['foo@example.com'],
        ]], (new Email)->process([[
            'id' => 'abc',
            'to' => ['foo@example.com'],
            'cc' => [],
            'subject' => '',
            'reply_to' => null,
        ]], $form));
    }

    #[Test]
    public function the_address_fields_can_be_set_to_form_fields()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'full_name', 'field' => ['type' => 'name']],
                ['handle' => 'email_address', 'field' => ['type' => 'email', 'display' => 'Email Address']],
                ['handle' => 'intro', 'field' => ['type' => 'heading']],
                ['handle' => 'address', 'field' => ['type' => 'group']],
            ],
        ]))->save();

        $meta = Email::blueprint($form)->fields()->meta();

        foreach (['to', 'cc', 'bcc', 'from', 'reply_to'] as $handle) {
            $this->assertEquals([
                ['value' => 'field:full_name', 'label' => 'Full Name', 'icon' => 'user-avatar-flush', 'category' => 'contact'],
                ['value' => 'field:email_address', 'label' => 'Email Address', 'icon' => 'mail-sign-at', 'category' => 'contact'],
            ], $meta->get($handle)['options']);
        }
    }

    private function makeForm(array $extraFields = [])
    {
        return tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                ['handle' => 'email', 'field' => ['type' => 'email', 'display' => 'Email']],
                ...$extraFields,
            ],
        ]))->save();
    }

    private function makeSubmission($form, array $data, string $date, bool $partial = false)
    {
        $submission = $form->makeSubmission()->id(Carbon::parse($date)->timestamp)->data($data);

        if ($partial) {
            $submission->asPartial();
        }

        return tap($submission)->save();
    }

    private function userWithEditPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);

        return tap(User::make()->assignRole('test'))->save();
    }
}
