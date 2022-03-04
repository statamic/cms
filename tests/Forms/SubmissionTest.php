<?php

namespace Tests\Forms;

use Illuminate\Support\Collection;
use Statamic\Facades\Blueprint;
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

    /** @test */
    public function it_sets_and_gets_data()
    {
        $submission = Form::make('test')->makeSubmission();

        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        Blueprint::shouldReceive('find')->with('forms.test')->andReturn($blueprint);

        $this->assertInstanceOf(Collection::class, $data = $submission->data());
        $this->assertEquals([], $data->all());
        $this->assertFalse($submission->has('foo'));
        $this->assertNull($submission->get('foo'));
        $this->assertNull($submission->foo);
        $this->assertFalse($submission->has('hello'));
        $this->assertNull($submission->get('hello'));
        $this->assertNull($submission->hello);

        $return = $submission->set('hello', 'world');

        $this->assertInstanceOf(Collection::class, $data = $submission->data());
        $this->assertEquals(['hello' => 'world'], $data->all());
        $this->assertEquals($submission, $return);
        $this->assertFalse($submission->has('foo'));
        $this->assertNull($submission->get('foo'));
        $this->assertNull($submission->foo);
        $this->assertTrue($submission->has('hello'));
        $this->assertEquals('world', $submission->get('hello'));
        $this->assertEquals('world', $submission->hello);

        $return = $submission->data(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertEquals($submission, $return);
        $this->assertInstanceOf(Collection::class, $data = $submission->data());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $data->all());
        $this->assertTrue($submission->has('foo'));
        $this->assertEquals('bar', $submission->get('foo'));
        $this->assertEquals('bar', $submission->foo);
        $this->assertFalse($submission->has('hello'));
        $this->assertNull($submission->get('hello'));
        $this->assertNull($submission->hello);
    }
}
