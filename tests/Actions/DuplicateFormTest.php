<?php

namespace Tests\Actions;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\DuplicateForm;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\TestCase;

class DuplicateFormTest extends TestCase
{
    use FakesRoles;

    public function setUp(): void
    {
        parent::setUp();

        Form::all()->each->delete();
    }

    public function tearDown(): void
    {
        Form::all()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_duplicates_a_form()
    {
        Form::make('a')->title('Original A')->honeypot('a')->save();
        Form::make('b')->title('Original B')->honeypot('b')->save();
        Form::make('c')->title('Original C')->honeypot('c')->save();

        $this->assertEquals([
            'a' => ['title' => 'Original A', 'honeypot' => 'a'],
            'b' => ['title' => 'Original B', 'honeypot' => 'b'],
            'c' => ['title' => 'Original C', 'honeypot' => 'c'],
        ], $this->formData());

        (new DuplicateForm)->run(
            collect([Form::find('b')]),
            ['title' => 'Duplicate of B', 'handle' => 'd']
        );

        $this->assertEquals([
            'a' => ['title' => 'Original A', 'honeypot' => 'a'],
            'b' => ['title' => 'Original B', 'honeypot' => 'b'],
            'c' => ['title' => 'Original C', 'honeypot' => 'c'],
            'd' => ['title' => 'Duplicate of B', 'honeypot' => 'b'],
        ], $this->formData());
    }

    #[Test]
    public function user_with_create_permission_is_authorized()
    {
        $this->setTestRoles([
            'access' => ['configure forms'],
            'noaccess' => [],
        ]);

        $userWithPermission = tap(User::make()->assignRole('access'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('noaccess'))->save();
        $items = collect([
            tap(Form::make('a')->title('Original A'))->save(),
            tap(Form::make('b')->title('Original B'))->save(),
        ]);

        $this->assertTrue((new DuplicateForm)->authorize($userWithPermission, $items->first()));
        $this->assertTrue((new DuplicateForm)->authorizeBulk($userWithPermission, $items));
        $this->assertFalse((new DuplicateForm)->authorize($userWithoutPermission, $items->first()));
        $this->assertFalse((new DuplicateForm)->authorizeBulk($userWithoutPermission, $items));
    }

    private function formData()
    {
        return Form::all()->mapWithKeys(fn ($form) => [$form->handle() => [
            'title' => $form->title(),
            'honeypot' => $form->honeypot(),
        ]])->all();
    }
}
