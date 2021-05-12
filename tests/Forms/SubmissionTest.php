<?php

namespace Tests\Forms;

use Statamic\Facades\Form;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    /** @test */
    public function the_id_is_generated_the_first_time_but_can_be_overridden()
    {
        $submission = Form::make('test')->makeSubmission();

        $this->assertNotNull($id = $submission->id());
        $this->assertEquals($id, $submission->id());
        $this->assertEquals($id, $submission->id());

        $submission->id('123');

        $this->assertEquals('123', $submission->id());
    }

    /** @test */
    public function generated_ids_dont_have_commas()
    {
        // this test becomes unnecessary if we ever move away from using microtime for ids.

        setlocale(LC_ALL, 'de_DE');

        $submission = Form::make('test')->makeSubmission();

        $this->assertStringNotContainsString(',', $submission->id());

        setlocale(LC_ALL, 'en_US');
    }
}
