<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->update($form)
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_updates_a_form()
    {
        $form = tap(Form::make('test')->title('Original title'))->save();
        $this->assertCount(1, Form::all());
        $this->assertEquals('Original title', $form->title());
        $this->assertEquals('honeypot', $form->honeypot());
        $this->assertTrue($form->store());

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, [
                'title' => 'Updated title',
                'honeypot' => 'color',
                'store' => false,
            ])
            ->assertOk();

        $this->assertCount(1, Form::all());
        $updated = Form::all()->first();
        $this->assertEquals('Updated title', $updated->title());
        $this->assertEquals('color', $updated->honeypot());
        $this->assertFalse($updated->store());
    }

    #[Test]
    public function it_updates_emails()
    {
        $form = tap(Form::make('test'))->save();
        $this->assertNull($form->email());

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['email' => [
                [
                    'to' => 'john@example.com',
                    'from' => 'jane@example.com',
                    'reply_to' => null,
                    'subject' => null,
                    'text' => null,
                    'html' => null,
                    'markdown' => false,
                    'attachments' => false,
                ],
                [
                    'to' => 'foo@example.com',
                    'from' => 'bar@example.com',
                    'reply_to' => null,
                    'subject' => null,
                    'text' => 'emails.contact.text',
                    'html' => 'emails.contact.html',
                    'markdown' => true,
                    'attachments' => true,
                ],
            ]])
            ->assertOk();

        $updated = Form::all()->first();
        $this->assertEquals([
            [
                'to' => 'john@example.com',
                'from' => 'jane@example.com',
            ],
            [
                'to' => 'foo@example.com',
                'from' => 'bar@example.com',
                'text' => 'emails.contact.text',
                'html' => 'emails.contact.html',
                'markdown' => true,
                'attachments' => true,
            ],
        ], $updated->email());
    }

    /** @test */
    public function it_updates_data()
    {
        $form = tap(Form::make('test'))->save();
        $this->assertNull($form->email());

        Form::appendConfigFields('*', 'Test Config', [
            'another_config' => [
                'handle' => 'another_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
            'some_config' => [
                'handle' => 'some_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
        ]);

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['some_config' => 'foo', 'another_config' => 'bar'])
            ->assertOk();

        $updated = Form::all()->first();
        $this->assertEquals([
            'another_config' => 'bar',
            'some_config' => 'foo',
        ], $updated->data()->all());
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function update($form, $params = [])
    {
        $params = array_merge([
            'title' => 'Updated title',
        ], $params);

        return $this->patch(cp_route('forms.update', $form->handle()), $params);
    }
}
