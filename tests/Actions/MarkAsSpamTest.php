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
    public function it_is_not_visible_to_partial_submissions()
    {
        $partial = $this->form->makeSubmission()->asPartial();
        $honeypot = $this->form->makeSubmission()->asPartial()->markAsSpam();

        $this->assertFalse((new MarkAsSpam)->visibleTo($partial));
        $this->assertFalse((new MarkAsSpam)->visibleTo($honeypot));
    }

    #[Test]
    public function it_is_not_authorized_for_partial_submissions_even_for_super_users()
    {
        $user = tap(User::make()->makeSuper())->save();
        $partial = tap($this->form->makeSubmission()->asPartial()->data(['name' => 'Olaf']))->save();
        $finalized = tap($this->form->makeSubmission()->data(['name' => 'Olaf']))->save();

        $this->assertFalse((new MarkAsSpam)->authorize($user, $partial));
        $this->assertTrue((new MarkAsSpam)->authorize($user, $finalized));
    }

    #[Test]
    public function it_is_not_available_in_bulk_when_the_selection_includes_a_partial_submission()
    {
        $user = tap(User::make()->makeSuper())->save();
        $partial = tap($this->form->makeSubmission()->asPartial()->data(['name' => 'Olaf']))->save();
        $finalized = tap($this->form->makeSubmission()->data(['name' => 'Anna']))->save();

        $this->assertTrue((new MarkAsSpam)->visibleToBulk(collect([$finalized])));
        $this->assertTrue((new MarkAsSpam)->authorizeBulk($user, collect([$finalized])));
        $this->assertFalse((new MarkAsSpam)->visibleToBulk(collect([$finalized, $partial])));
        $this->assertFalse((new MarkAsSpam)->authorizeBulk($user, collect([$finalized, $partial])));
    }

    #[Test]
    public function it_requires_permission_to_mark_submissions_as_spam()
    {
        $this->setTestRoles([
            'global' => ['view form submissions', 'mark form submissions as spam'],
            'form' => ['view contact form submissions', 'mark contact form submissions as spam'],
            'other_form' => ['view contact form submissions', 'mark other form submissions as spam'],
            'view_only' => ['view form submissions', 'view contact form submissions'],
            'noaccess' => [],
        ]);

        $submission = tap($this->form->makeSubmission()->data(['name' => 'Olaf']))->save();

        foreach (['global', 'form'] as $role) {
            $user = tap(User::make()->assignRole($role))->save();
            $this->assertTrue((new MarkAsSpam)->authorize($user, $submission), $role);
        }

        foreach (['other_form', 'view_only', 'noaccess'] as $role) {
            $user = tap(User::make()->assignRole($role))->save();
            $this->assertFalse((new MarkAsSpam)->authorize($user, $submission), $role);
        }
    }
}
