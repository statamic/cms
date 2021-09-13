<?php

namespace Tests\Antlers\Runtime\Libraries;

use Facade\Ignition\Exceptions\ViewException;
use Tests\Antlers\ParserTestCase;

class BasicLibraryTest extends ParserTestCase
{
    public function test_named_arguments_work()
    {
        $data = [
            'test' => ['one', 'two', 'three'],
        ];

        $this->assertSame('one, two and three', $this->renderString('{{ str.sentenceList(test, oxford_comma: false) }}', $data));
        $this->assertSame('one, two, and three', $this->renderString('{{ str.sentenceList(test, oxford_comma: true) }}', $data));
        $this->assertSame('one, two,  test  three', $this->renderString('{{ str.sentenceList(test, oxford_comma: true, glue: " test ") }}', $data));
        $this->assertSame('one, two,  test  three', $this->renderString('{{ str.sentenceList(test, " test ", true) }}', $data));
    }

    public function test_attempting_to_use_named_args_on_parameters_without_default_throws_exception()
    {
        $this->expectException(ViewException::class);
        $this->renderString('{{ str.sentenceList(list: test) }}');
    }

    public function test_invalid_named_arg_type_throws_exception()
    {
        $this->expectException(ViewException::class);
        $this->renderString('{{ str.sentenceList(list, "string": value) }}');
    }

    public function test_calling_method_with_invalid_named_argument_throws_exception()
    {
        $this->expectException(ViewException::class);

        $data = [
            'test' => ['one', 'two', 'three'],
        ];

        $this->renderString('{{ str.sentenceList(test, not_a_valid_name: test }}', $data);
    }

    public function test_calling_method_with_incorrect_type_throws_exception()
    {
        $this->expectException(ViewException::class);

        $this->renderString('{{ str.sentenceList("string") }}');
    }

    public function test_calling_method_without_enough_arguments_throws_exception()
    {
        $this->expectException(ViewException::class);
        $this->renderString('{{ str.sentenceList() }}');
    }

    public function test_calling_method_with_too_many_arguments_throws_exception()
    {
        $this->expectException(ViewException::class);
        $this->renderString('{{ str.sentenceList(this, is, too, many) }}');
    }

    public function test_method_call_results_can_be_used_as_input()
    {
        $this->assertSame('one, two and three', $this->renderString('{{ str.sentenceList(arr.explode(",", "one,two,three"), oxford_comma: false) }}'));
    }
}
