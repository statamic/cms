<?php

namespace Tests\Forms;

use Mockery as m;
use Statamic\Facades\Antlers;
use Statamic\Forms\Email;
use Statamic\Forms\Submission;
use Tests\TestCase;

class EmailTest extends TestCase
{
    /** @test */
    public function it_sets_html_view_as_string()
    {
        /** @var Submission */
        $submission = m::mock(Submission::class);
        $submission->shouldReceive('toArray')->andReturn([]);
        $submission->shouldReceive('toAugmentedArray')->andReturn([]);

        $email = new Email($submission, [
            'to' => Antlers::parse('test@example.com'),
            'html' => Antlers::parse('emails/test'),
        ]);

        $email->build();

        $this->assertTrue(is_string($email->view), 'View is not a string.');
        $this->assertEquals('emails/test', $email->view);
    }
}
