<?php

namespace Tests\Forms\Connections;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Forms\Connections\Rules\EmailConnectionAddress;
use Tests\TestCase;

class EmailConnectionAddressRuleTest extends TestCase
{
    #[Test]
    #[DataProvider('validAddressProvider')]
    public function it_passes($value)
    {
        $this->assertTrue($this->validate($value)->passes());
    }

    #[Test]
    #[DataProvider('invalidAddressProvider')]
    public function it_fails($value, $error)
    {
        $validator = $this->validate($value);

        $this->assertTrue($validator->fails());
        $this->assertEquals($error, $validator->errors()->first('address'));
    }

    public static function validAddressProvider()
    {
        return [
            'email' => ['foo@example.com'],
            'email with name' => ['Foo Bar <foo@example.com>'],
            'comma-separated emails' => ['foo@example.com, Bar <bar@example.com>'],
            'antlers' => ['{{ company:email }}'],
            'comma-separated emails with antlers' => ['{{ company:email }}, foo@example.com'],
            'field reference' => ['field:email'],
            'array of addresses' => [['foo@example.com', 'Bar <bar@example.com>', '{{ company:email }}', 'field:email']],
            'null' => [null],
            'empty string' => [''],
            'empty array' => [[]],
        ];
    }

    public static function invalidAddressProvider()
    {
        return [
            'not an email' => ['nope', 'Must be a valid email address.'],
            'invalid email in comma-separated list' => ['foo@example.com, nope', 'Must be a valid email address.'],
            'malformed name syntax' => ['Foo Bar <foo@', 'Must be a valid email address.'],
            'unknown field reference' => ['field:nonexistent', "References a field that doesn't exist on this form."],
            'array with an invalid address' => [['foo@example.com', 'nope'], 'Must be a valid email address.'],
            'array with an unknown field reference' => [['foo@example.com', 'field:nonexistent'], "References a field that doesn't exist on this form."],
            'not a string' => [[123], 'Must be a valid email address.'],
        ];
    }

    private function validate($value)
    {
        $form = Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'email']],
            ],
        ]);

        return Validator::make(['address' => $value], ['address' => new EmailConnectionAddress($form)]);
    }
}
