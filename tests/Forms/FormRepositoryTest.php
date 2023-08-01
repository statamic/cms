<?php

namespace Tests\Forms;

use Statamic\Facades\Form;
use Tests\TestCase;

class FormRepositoryTest extends TestCase
{
    /** @test */
    public function it_registers_config()
    {
        Form::addConfig('test_form', [
            'config' => [
                'display' => __('config'),
                'fields' => [
                    'another_config' => [
                        'handle' => 'another_config',
                        'field' => [
                            'type' => 'text',
                        ]
                    ],
                    'some_config' => [
                        'handle' => 'some_config',
                        'field' => [
                            'type' => 'text',
                        ]
                    ],
                ],
            ],
        ]);

        $this->assertNotNull(Form::getConfigFor('test_form'));
        $this->assertEmpty(Form::getConfigFor('another_form'));
    }

    /** @test */
    public function it_registers_wildcard_config()
    {
        Form::addConfig('*', [
            'config' => [
                'display' => __('config'),
                'fields' => [
                    'another_config' => [
                        'handle' => 'another_config',
                        'field' => [
                            'type' => 'text',
                        ]
                    ],
                    'some_config' => [
                        'handle' => 'some_config',
                        'field' => [
                            'type' => 'text',
                        ]
                    ],
                ],
            ],
        ]);

        $this->assertNotNull(Form::getConfigFor('test_form'));
        $this->assertNotNull(Form::getConfigFor('another_form'));
    }
}
