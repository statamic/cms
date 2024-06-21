<?php

namespace Tests\Tags;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class GetErrorsTest extends TestCase
{
    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    #[Test]
    #[DataProvider('organizedProvider')]
    public function it_gets_errors_organized_into_fields($params, $bag, $errors, $expected)
    {
        view()->share('errors', (new ViewErrorBag())->put($bag, new MessageBag($errors)));

        $template = <<<EOT
{{ get_errors $params }}
before
{{ fields }}
{{ field }}
{{ messages }}
- {{ message }}
{{ /messages }}
{{ /fields }}
after
{{ /get_errors }}
EOT;

        $this->assertEquals($expected, $this->tag($template));
    }

    public static function organizedProvider()
    {
        $filledExpectation = <<<'EOT'

before

name

- name is required

- name should be 10 chars


email

- email should be an email


after

EOT;

        $messages = [
            'name' => ['name is required', 'name should be 10 chars'],
            'email' => ['email should be an email'],
        ];

        return [
            'empty bag' => [
                '',
                'default',
                [],
                '',
            ],
            'filled default bag' => [
                '',
                'default',
                $messages,
                $filledExpectation,
            ],
            'filled custom bag' => [
                ' bag="custom"',
                'custom',
                $messages,
                $filledExpectation,
            ],
        ];
    }

    #[Test]
    #[DataProvider('allProvider')]
    public function it_gets_errors_for_all_fields_together($params, $bag, $errors, $expected)
    {
        view()->share('errors', (new ViewErrorBag())->put($bag, new MessageBag($errors)));

        $template = <<<EOT
{{ get_errors:all $params }}
before
{{ messages }}
- {{ message }}
{{ /messages }}
after
{{ /get_errors:all }}
EOT;

        $this->assertEquals($expected, $this->tag($template));
    }

    public static function allProvider()
    {
        $filledExpectation = <<<'EOT'

before

- name is required

- name should be 10 chars

- email should be an email

after

EOT;

        $messages = [
            'name' => ['name is required', 'name should be 10 chars'],
            'email' => ['email should be an email'],
        ];

        return [
            'empty bag' => [
                '',
                'default',
                [],
                '',
            ],
            'filled default bag' => [
                '',
                'default',
                $messages,
                $filledExpectation,
            ],
            'filled custom bag' => [
                ' bag="custom"',
                'custom',
                $messages,
                $filledExpectation,
            ],
        ];
    }

    #[Test]
    #[DataProvider('fieldProvider')]
    public function it_gets_errors_for_a_single_field($params, $bag, $errors, $expected)
    {
        view()->share('errors', (new ViewErrorBag())->put($bag, new MessageBag($errors)));

        $template = <<<EOT
{{ get_errors:name $params }}
before
{{ messages }}
- {{ message }}
{{ /messages }}
after
{{ /get_errors:name }}
EOT;

        $this->assertEquals($expected, $this->tag($template));
    }

    public static function fieldProvider()
    {
        $filledExpectation = <<<'EOT'

before

- name is required

- name should be 10 chars

after

EOT;

        $messages = [
            'name' => ['name is required', 'name should be 10 chars'],
            'email' => ['email should be an email'],
        ];

        return [
            'empty bag' => [
                '',
                'default',
                [],
                '',
            ],
            'filled default bag' => [
                '',
                'default',
                $messages,
                $filledExpectation,
            ],
            'filled custom bag' => [
                ' bag="custom"',
                'custom',
                $messages,
                $filledExpectation,
            ],
            'filled default bag but not for given field' => [
                '',
                'default',
                ['email' => ['email should be an email']],
                '',
            ],
            'filled custom bag but not for given field' => [
                ' bag="custom"',
                'custom',
                ['email' => ['email should be an email']],
                '',
            ],
        ];
    }
}
