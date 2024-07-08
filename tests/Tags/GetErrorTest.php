<?php

namespace Tests\Tags;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class GetErrorTest extends TestCase
{
    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    #[Test]
    public function the_tag_by_itself_does_nothing()
    {
        view()->share('errors', (new ViewErrorBag())->put('default', new MessageBag([])));

        $this->assertEquals(
            '',
            $this->tag('{{ get_error }}before {{ message }} after{{ /get_error }}')
        );
    }

    #[Test]
    public function it_outputs_nothing_when_the_field_doesnt_have_an_error()
    {
        view()->share('errors', (new ViewErrorBag())->put('default', new MessageBag([])));

        $this->assertEquals(
            '',
            $this->tag('{{ get_error:email }}before {{ message }} after{{ /get_error:email }}')
        );
    }

    #[Test]
    public function it_outputs_nothing_when_there_are_errors_but_not_for_the_given_field_in_a_specific_bag()
    {
        view()->share('errors', (new ViewErrorBag())->put('custom', new MessageBag([
            'name' => ['name is required'],
        ])));

        $this->assertEquals(
            '',
            $this->tag('{{ get_error:email bag="custom" }}before {{ message }} after{{ /get_error:email }}')
        );
    }

    #[Test]
    public function it_outputs_nothing_when_the_field_doesnt_have_an_error_for_specific_bag()
    {
        view()->share('errors', (new ViewErrorBag())->put('custom', new MessageBag([])));

        $this->assertEquals(
            '',
            $this->tag('{{ get_error:email bag="custom" }}before {{ message }} after{{ /get_error:email }}')
        );
    }

    #[Test]
    public function it_outputs_nothing_when_there_are_errors_but_not_for_the_given_field()
    {
        view()->share('errors', (new ViewErrorBag())->put('default', new MessageBag([
            'name' => ['name is required'],
        ])));

        $this->assertEquals(
            '',
            $this->tag('{{ get_error:email }}before {{ message }} after{{ /get_error:email }}')
        );
    }

    #[Test]
    public function it_gets_the_first_error_for_a_single_field()
    {
        view()->share('errors', (new ViewErrorBag())->put('default', new MessageBag([
            'name' => ['name is required'],
            'email' => ['email is required', 'email should be an email address'],
        ])));

        $this->assertEquals(
            'before email is required after',
            $this->tag('{{ get_error:email }}before {{ message }} after{{ /get_error:email }}')
        );
    }

    #[Test]
    public function it_gets_the_first_error_for_a_single_field_in_given_bag()
    {
        view()->share('errors', (new ViewErrorBag())->put('custom', new MessageBag([
            'name' => ['name is required'],
            'email' => ['email is required', 'email should be an email address'],
        ])));

        $this->assertEquals(
            'before email is required after',
            $this->tag('{{ get_error:email bag="custom" }}before {{ message }} after{{ /get_error:email }}')
        );
    }
}
