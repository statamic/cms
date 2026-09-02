<?php

namespace Tests\Actions;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\MarkAsSpam;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MarkAsSpamTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = tap(Form::make('contact'))->save();
    }

    public function tearDown(): void
    {
        $this->form->submissions()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_marks_submissions_as_spam()
    {
        $submission = tap($this->form->makeSubmission()->data(['name' => 'Olaf']))->save();

        (new MarkAsSpam)->run(collect([$submission]), []);

        $submission = $this->form->submission($submission->id());

        $this->assertTrue($submission->isSpam());
        $this->assertEquals('spam', $submission->status());
    }

    #[Test]
    public function it_is_only_visible_to_submissions_that_are_not_spam()
    {
        $submission = $this->form->makeSubmission();
        $spam = $this->form->makeSubmission()->markAsSpam();

        $this->assertTrue((new MarkAsSpam)->visibleTo($submission));
        $this->assertFalse((new MarkAsSpam)->visibleTo($spam));
        $this->assertFalse((new MarkAsSpam)->visibleTo($this->form));
    }

    #[Test]
    public function it_requires_permission_to_view_submissions()
    {
        $this->setTestRoles([
            'access' => ['view form submissions'],
            'noaccess' => [],
        ]);

        $userWithPermission = tap(User::make()->assignRole('access'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('noaccess'))->save();
        $submission = tap($this->form->makeSubmission()->data(['name' => 'Olaf']))->save();

        $this->assertTrue((new MarkAsSpam)->authorize($userWithPermission, $submission));
        $this->assertFalse((new MarkAsSpam)->authorize($userWithoutPermission, $submission));
    }
}
