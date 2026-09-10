<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
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

    protected function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false)->byDefault();
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
    public function it_updates_restrictions()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, [
                'submission_limit' => 5,
                'submission_limit_period' => 'day',
                'closed_message' => 'Sorry, we are isClosed.',
                'require_login' => true,
            ])
            ->assertOk();

        $updated = Form::all()->first();
        $this->assertEquals(5, $updated->get('submission_limit'));
        $this->assertEquals('day', $updated->get('submission_limit_period'));
        $this->assertEquals('Sorry, we are isClosed.', $updated->get('closed_message'));
        $this->assertTrue($updated->get('require_login'));
    }

    #[Test]
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
