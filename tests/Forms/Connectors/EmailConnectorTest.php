<?php

namespace Tests\Forms\Connectors;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Form;
use Statamic\Facades\FormConnector;
use Statamic\Facades\User;
use Statamic\Forms\Connectors\Email;
use Statamic\Forms\FakeSubmissionGenerator;
use Statamic\Forms\SendEmail;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EmailConnectorTest extends TestCase
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
    public function it_returns_a_job_per_email()
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', 'to' => ['first@example.com']],
            ['id' => 'two', 'to' => ['second@example.com']],
        ]]))->save();

        $jobs = (new Email)->forForm($form)->finalized($form->makeSubmission());

        $this->assertCount(2, $jobs);
        $this->assertContainsOnlyInstancesOf(SendEmail::class, $jobs);
        $this->assertEquals('one', $jobs[0]->config['id']);
        $this->assertEquals('two', $jobs[1]->config['id']);
    }

    #[Test]
    public function it_uses_the_configured_send_email_job()
    {
        config(['statamic.forms.send_email_job' => CustomSendEmail::class]);

        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', 'to' => ['first@example.com']],
        ]]))->save();

        $jobs = (new Email)->forForm($form)->finalized($form->makeSubmission());

        $this->assertContainsOnlyInstancesOf(CustomSendEmail::class, $jobs);
    }

    #[Test]
    #[DataProvider('sendEmailJobProvider')]
    public function it_passes_addresses_to_the_job_as_comma_separated_strings(string $class)
    {
        config(['statamic.forms.send_email_job' => $class]);

        $form = tap(Form::make('test')->connections(['email' => [
            [
                'id' => 'one',
                'to' => ['field:email', 'first@example.com'],
                'cc' => ['cc@example.com'],
                'bcc' => ['{{ x ?? "a@example.com, b@example.com" }}', 'bcc@example.com'],
                'reply_to' => ['field:email'],
                'from' => 'sender@example.com',
            ],
            ['id' => 'two', 'to' => 'legacy@example.com, other@example.com'],
        ]]))->save();

        $jobs = (new Email)->forForm($form)->finalized($form->makeSubmission());

        $this->assertContainsOnlyInstancesOf($class, $jobs);
        $this->assertSame([
            'id' => 'one',
            'to' => 'field:email, first@example.com',
            'cc' => 'cc@example.com',
            'bcc' => '{{ x ?? "a@example.com, b@example.com" }}, bcc@example.com',
            'reply_to' => 'field:email',
            'from' => 'sender@example.com',
        ], $jobs[0]->config);
        $this->assertSame(['id' => 'two', 'to' => 'legacy@example.com, other@example.com'], $jobs[1]->config);
    }

    public static function sendEmailJobProvider(): array
    {
        return [
            'core job' => [SendEmail::class],
            'custom job' => [CustomSendEmail::class],
        ];
    }

    #[Test]
    public function it_filters_emails_using_conditions_when_finalized()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'department', 'field' => ['type' => 'text']],
            ],
        ])->connections(['email' => [
            ['id' => 'sales', 'to' => ['sales@example.com'], 'conditions' => [['field' => 'department', 'operator' => 'equals', 'value' => 'sales', 'join' => 'and']]],
            ['id' => 'support', 'to' => ['support@example.com'], 'conditions' => [['field' => 'department', 'operator' => 'equals', 'value' => 'support', 'join' => 'and']]],
            ['id' => 'disabled', 'to' => ['disabled@example.com'], 'enabled' => false],
        ]]))->save();

        $jobs = (new Email)->forForm($form)->finalized($form->makeSubmission()->data(['department' => 'support']));

        $this->assertCount(1, $jobs);
        $this->assertEquals('support', $jobs[0]->config['id']);
    }

    #[Test]
    #[DataProvider('emailCountProvider')]
    public function it_counts_the_configured_emails(array $emails, int $count)
    {
        $form = tap(Form::make('test')->connections(['email' => $emails]))->save();

        $this->assertEquals($count, (new Email)->forForm($form)->count());
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

        $component = (new Email)->forForm($form)->render()->toArray();

        $this->assertEquals('email-connector', $component['name']);
        $this->assertEquals(['blueprint', 'meta', 'defaults', 'previewUrl'], array_keys($component['props']));
        $this->assertEquals(['one', 'two'], array_keys($component['props']['meta']));
        $this->assertEquals([], $component['props']['defaults']['values']['to']);
        $this->assertArrayHasKey('meta', $component['props']['defaults']);
        $this->assertEquals(cp_route('forms.connect.email.preview', 'test'), $component['props']['previewUrl']);
    }

    #[Test]
    public function it_links_to_assets_in_the_configured_container()
    {
        AssetContainer::make('documents')->disk('local')->save();
        AssetContainer::make('images')->disk('local')->save();

        $form = tap(Form::make('test'))->save();
        $email = (new Email)->forForm($form);

        $this->assertNull($email->blueprint()->field('body')->config()['container']);

        config(['statamic.forms.email_link_container' => 'documents']);

        $this->assertEquals('documents', $email->blueprint()->field('body')->config()['container']);
    }

    #[Test]
    public function it_registers_a_preview_route()
    {
        FormConnector::routes();

        $route = collect(Route::getRoutes())->first(fn ($route) => $route->getName() === 'forms.connect.email.preview');

        $this->assertNotNull($route);
        $this->assertEquals('forms/{form}/connect/email/preview', $route->uri());
        $this->assertContains('POST', $route->methods());
        $this->assertContains('can:editConnections,form', $route->middleware());
    }

    #[Test]
    public function it_previews_an_email_using_a_sample_submission()
    {
        $form = $this->makeForm();

        $response = $this
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email', 'Team <team@example.com>'],
                'from' => ['sender@example.com'],
                'reply_to' => ['field:email'],
                'subject' => 'Hello [[name]]',
            ])
            ->assertOk()
            ->assertJson([
                'from' => ['sender@example.com'],
                'format' => 'html',
                'cc' => [],
                'bcc' => [],
                'sample' => true,
            ]);

        $this->assertEquals('Hello Jane Doe', $response->json('subject'));
        $this->assertEquals(['name@example.com', 'Team <team@example.com>'], $response->json('to'));
        $this->assertEquals(['name@example.com'], $response->json('reply_to'));
        $this->assertStringContainsString('<b>Name:</b>', $response->json('body'));
        $this->assertStringContainsString('<b>Email:</b>', $response->json('body'));
        $this->assertEquals(0, $form->querySubmissions()->count());
    }

    #[Test]
    public function it_previews_a_sample_submission_without_the_fake_submission_generator()
    {
        $this->app->bind(FakeSubmissionGenerator::class, fn () => throw new \Exception('The fake submission generator should not be used.'));

        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email'],
                'subject' => 'Hello [[name]]',
            ])
            ->assertOk()
            ->assertJson([
                'subject' => 'Hello Jane Doe',
                'to' => ['name@example.com'],
                'sample' => true,
            ]);
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
                'subject' => 'Hello [[name]]',
            ])
            ->assertOk()
            ->assertJson([
                'subject' => 'Hello Latest',
                'to' => ['latest@example.com'],
                'sample' => false,
            ]);
    }

    #[Test]
    #[DataProvider('editOnlyPermissionsProvider')]
    public function it_denies_the_preview_to_users_who_cannot_view_submissions(array $permissions)
    {
        $form = $this->makeForm();
        $this->makeSubmission($form, ['name' => 'Latest', 'email' => 'latest@example.com'], '2026-02-01 10:00');

        $this->setTestRoles(['test' => ['access cp', ...$permissions]]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email'],
                'subject' => 'Hello [[name]]',
            ])
            ->assertForbidden();
    }

    public static function editOnlyPermissionsProvider(): array
    {
        return [
            'edit forms' => [['edit forms']],
            'edit test form' => [['edit test form']],
        ];
    }

    #[Test]
    #[DataProvider('templateSyntaxProvider')]
    public function it_rejects_template_syntax_in_the_preview_from_untrusted_users(string $key, mixed $value)
    {
        config(['database.connections.secret' => ['password' => 'hunter2']]);

        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email'],
                $key => $value,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["0.{$key}"])
            ->assertDontSee('hunter2');
    }

    public static function templateSyntaxProvider(): array
    {
        return [
            'antlers in the subject' => ['subject', 'Hi {{ config:database.connections.secret.password }}'],
            'env in the subject' => ['subject', 'Hi {env:APP_KEY}'],
            'antlers in a recipient' => ['to', ['field:email', '{{ users }}{{ email }},{{ /users }}']],
            'antlers in the sender' => ['from', '{{ config:database.connections.secret.password }}@example.com'],
            'antlers in the reply to' => ['reply_to', ['{{ config:app.name }}']],
            'antlers in the cc' => ['cc', ['{{ config:app.name }}']],
            'antlers in the bcc' => ['bcc', ['{{ config:app.name }}']],
            'antlers in the html view' => ['html', '{{ config:app.name }}'],
            'antlers in the text view' => ['text', '{{ config:app.name }}'],
            'antlers in the mailer' => ['mailer', '{{ config:app.name }}'],
            'antlers joined by an unknown mention' => ['subject', 'Hi {[[nope]]{ config:database.connections.secret.password }}'],
            'env joined by an unknown mention' => ['subject', 'Hi {[[nope]]env:APP_KEY}'],
            'antlers joined by an escaped mention' => ['subject', 'Hi {[[ nope ]]{[[ nope ]] config:app.name }[[nope]]}'],
        ];
    }

    #[Test]
    public function it_allows_template_syntax_in_the_preview_from_users_who_can_configure_forms()
    {
        config(['app.name' => 'Acme']);

        $form = $this->makeForm();

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email'],
                'subject' => 'Hello {{ config:app.name }}',
            ])
            ->assertOk()
            ->assertJson(['subject' => 'Hello Acme']);
    }

    #[Test]
    public function it_allows_unchanged_stored_template_syntax_in_the_preview_from_untrusted_users()
    {
        config(['app.name' => 'Acme']);

        $form = tap($this->makeForm()->connections([
            'email' => [['id' => 'abc', 'to' => ['field:email', '{{ config:mail.from.address }}'], 'subject' => 'Hello {{ config:app.name }}']],
        ]))->save();

        $this
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['field:email', '{{ config:mail.from.address }}', 'new@example.com'],
                'subject' => 'Hello {{ config:app.name }}',
            ])
            ->assertOk()
            ->assertJson(['subject' => 'Hello Acme']);
    }

    #[Test]
    public function it_previews_with_the_default_sender_when_none_is_configured()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithConnectionPermissions())
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
            ->actingAs($this->userWithConnectionPermissions())
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
            ->actingAs($this->userWithConnectionPermissions())
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
            ->actingAs($this->userWithConnectionPermissions())
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
            ->actingAs($this->userWithConnectionPermissions())
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
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
                'html' => 'emails.missing',
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'View [emails.missing] not found.']);
    }

    #[Test]
    public function it_validates_the_config_before_previewing()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'subject' => 'Hello',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['0.to']);

        $this
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['not-an-email'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['0.to']);
    }

    #[Test]
    #[DataProvider('invalidViewProvider')]
    public function it_only_allows_views_the_template_field_offers(?string $folder, string $view)
    {
        config(['statamic.forms.email_view_folder' => $folder]);

        $form = $this->makeForm();

        foreach (['html', 'text'] as $key) {
            $this
                ->actingAs($this->userWithConnectionPermissions())
                ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                    'id' => 'abc',
                    'to' => ['recipient@example.com'],
                    $key => $view,
                ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(["0.{$key}"]);
        }
    }

    public static function invalidViewProvider(): array
    {
        return [
            'namespaced view' => [null, 'statamic::forms.fields'],
            'view outside the folder' => ['emails', 'other.preview'],
            'view outside the folder using slashes' => ['emails', 'other/preview'],
            'folder name as a prefix of another folder' => ['emails', 'emails-old.preview'],
        ];
    }

    #[Test]
    public function it_allows_views_inside_the_configured_folder()
    {
        config(['statamic.forms.email_view_folder' => 'emails']);

        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithConnectionPermissions())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['recipient@example.com'],
                'html' => 'emails/preview',
            ])
            ->assertOk();
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
    public function it_renders_the_bound_connections_rather_than_the_stored_ones()
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'stored', 'to' => ['stored@example.com']],
        ]]))->save();

        $component = (new Email)->setForm($form)->setConnections([
            ['id' => 'override', 'to' => ['override@example.com']],
        ])->render()->toArray();

        $this->assertEquals(['override'], array_keys($component['props']['meta']));
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

        $configs = (new Email)->setForm($form)->preProcess($form->connections()->get('email'));

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

        $configs = (new Email)->setForm($form)->preProcess($form->connections()->get('email'));

        $this->assertEquals(['first@example.com', 'second@example.com'], $configs[0][$handle]);
    }

    #[Test]
    public function it_doesnt_split_legacy_address_strings_inside_antlers()
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', 'to' => '{{ x ?? "a@example.com, b@example.com" }}, {{ partial:foo a=\'1,2\' }}, c@example.com'],
        ]]))->save();

        $configs = (new Email)->setForm($form)->preProcess($form->connections()->get('email'));

        $this->assertEquals([
            '{{ x ?? "a@example.com, b@example.com" }}',
            "{{ partial:foo a='1,2' }}",
            'c@example.com',
        ], $configs[0]['to']);
    }

    #[Test]
    public function legacy_address_strings_are_converted_before_meta_is_built()
    {
        $form = tap(Form::make('test'))->save();

        $email = new class extends Email
        {
            public array $metaValues = [];

            protected function connectionMeta(array $connection): array
            {
                $fields = $this->connectionFields($connection);
                $this->metaValues[$connection['id']] = $fields->values()->only(['to', 'cc'])->all();

                return $fields->meta()->all();
            }
        };

        $email->setForm($form)->setConnections([['id' => 'one', 'to' => 'a@example.com, b@example.com', 'cc' => 'c@example.com']]);
        $props = $email->render()->toArray()['props'];

        $this->assertEquals(['one'], array_keys($props['meta']));
        $this->assertEquals(['to' => ['a@example.com', 'b@example.com'], 'cc' => ['c@example.com']], $email->metaValues['one']);
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
        ], (new Email)->setForm($form)->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    #[DataProvider('invalidConfigs')]
    public function it_rejects_invalid_configs($configs, $errors)
    {
        $form = tap(Form::make('test'))->save();

        $validator = Validator::make($configs, (new Email)->setForm($form)->rules());

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
        ], (new Email)->setForm($form)->process([
            ['id' => 'abc', 'to' => ['new@example.com', 'field:email'], 'subject' => 'Updated', 'enabled' => true, 'markdown' => false, 'attachments' => false],
            ['id' => 'def', 'to' => ['another@example.com'], 'enabled' => false, 'conditions' => [
                ['_id' => 'vue-row', 'field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
            ]],
            ['id' => 'ghi', 'to' => ['third@example.com'], 'markdown' => true, 'attachments' => true],
        ]));
    }

    #[Test]
    public function it_doesnt_persist_the_client_row_state()
    {
        $form = tap(Form::make('test'))->save();

        $this->assertEquals([
            ['id' => 'abc', 'to' => ['foo@example.com']],
        ], (new Email)->setForm($form)->process([
            ['_id' => 'vue-row', 'id' => 'abc', 'to' => ['foo@example.com']],
        ]));
    }

    #[Test]
    public function it_generates_an_id_for_configs_that_dont_have_one()
    {
        $form = tap(Form::make('test'))->save();

        $config = (new Email)->setForm($form)->process([['to' => ['foo@example.com']]])[0];

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
        ]], (new Email)->setForm($form)->process([[
            'id' => 'abc',
            'to' => ['foo@example.com'],
            'cc' => [],
            'subject' => '',
            'reply_to' => null,
        ]]));
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

        $meta = (new Email)->setForm($form)->blueprint()->fields()->meta();

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

    private function userWithConnectionPermissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);

        return tap(User::make()->assignRole('test'))->save();
    }
}

class CustomSendEmail extends SendEmail
{
}
