<?php

namespace Tests\Forms\Connections;

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
        $this->assertEquals(cp_route('forms.connect.email.update', 'test'), $component['props']['action']);
        $this->assertEquals(['one', 'two'], array_keys($component['props']['emails']));
        $this->assertEquals(['first@example.com'], $component['props']['emails']['one']['values']['to']);
        $this->assertEquals('First', $component['props']['emails']['one']['values']['subject']);
        $this->assertEquals(['second@example.com'], $component['props']['emails']['two']['values']['to']);
        $this->assertEquals('Second', $component['props']['emails']['two']['values']['subject']);
        $this->assertEquals([], $component['props']['defaults']['values']['to']);
        $this->assertArrayHasKey('meta', $component['props']['defaults']);
    }

    #[Test]
    #[DataProvider('legacyAddressProvider')]
    public function it_converts_legacy_address_strings_into_arrays(string $handle)
    {
        $form = tap(Form::make('test')->connections(['email' => [
            ['id' => 'one', $handle => 'first@example.com, second@example.com'],
        ]]))->save();

        $component = (new Email)->render($form)->toArray();

        $this->assertEquals(
            ['first@example.com', 'second@example.com'],
            $component['props']['emails']['one']['values'][$handle]
        );
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
