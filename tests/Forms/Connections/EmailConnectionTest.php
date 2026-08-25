<?php

namespace Tests\Forms\Connections;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Forms\Connections\Email;
use Statamic\Forms\SendEmails;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EmailConnectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_a_job_that_sends_the_emails()
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', 'to' => ['first@example.com']],
            ['id' => 'two', 'to' => ['second@example.com']],
        ]]))->save();

        $this->assertInstanceOf(SendEmails::class, (new Email)->finalized($form->makeSubmission()));
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
        $this->assertEquals(['blueprint', 'meta', 'defaults'], array_keys($component['props']));
        $this->assertEquals(['one', 'two'], array_keys($component['props']['meta']));
        $this->assertEquals([], $component['props']['defaults']['values']['to']);
        $this->assertArrayHasKey('meta', $component['props']['defaults']);
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
}
