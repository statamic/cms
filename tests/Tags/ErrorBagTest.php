<?php

namespace Tests\Tags;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Statamic\Facades\Parse;
use Tests\TestCase;

class ErrorBagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_returns_empty_string_with_empty_view_error_bag()
    {
        view()->share('errors', new ViewErrorBag);
        $this->assertEquals('', $this->tag('{{ error_bag }}{{ fields }}{{ field }}: {{ field_errors }}{{ value }}{{ /field_errors}}{{ /fields }}{{ /error_bag }}'));
    }

    /** @test */
    public function it_returns_single_error_from_single_error_field()
    {
        $messageBag = (new MessageBag())->add('name', 'name is required');
        $errorBag = (new ViewErrorBag())->put('default', $messageBag);

        view()->share('errors', $errorBag);

        $this->assertEquals(
            'name is required',
            $this->tag('{{ error_bag:name }}{{ field_error }}{{ /error_bag:name }}')
        );
    }

    /** @test */
    public function it_returns_multiple_errors_from_single_error_field()
    {
        $messageBag = (new MessageBag())
            ->add('name', 'name is required')
            ->add('name', 'name should be 10 chars');
        $errorBag = (new ViewErrorBag())->put('default', $messageBag);

        view()->share('errors', $errorBag);

        $this->assertEquals(
            'name is requiredname should be 10 chars',
            $this->tag('{{ error_bag:name }}{{ field_error }}{{ /error_bag:name }}')
        );
    }

    /** @test */
    public function it_returns_single_errors_from_multiple_error_fields()
    {
        $messageBag = (new MessageBag())
            ->add('name', 'name is required')
            ->add('number', 'number should be a number');
        $errorBag = (new ViewErrorBag())->put('default', $messageBag);

        view()->share('errors', $errorBag);

        $this->assertEquals(
            'name: name is requirednumber: number should be a number',
            $this->tag('{{ error_bag }}{{ fields }}{{ field }}: {{ field_errors }}{{ value }}{{ /field_errors}}{{ /fields }}{{ /error_bag }}')
        );
    }
}
