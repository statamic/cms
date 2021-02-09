<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class SelectFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_values_and_labels_of_single_select_field()
    {
        $field = [
            'type' => 'select',
            'multiple' => false,
            'options' => [
                'first' => 'Label of First',
                'second' => 'Label of Second',
            ],
        ];

        $this->createEntryWithFields([
            'filled' => [
                'value' => 'second',
                'field' => $field,
            ],
            'undefined' => [
                'value' => null,
                'field' => $field,
            ],
        ]);

        $query = <<<'GQL'
{
    entry(id: "1") {
        ... on Entry_Test_Blueprint {
            filled {
                label
                value
            }
            undefined {
                label
                value
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'filled' => [
                        'value' => 'second',
                        'label' => 'Label of Second',
                    ],
                    'undefined' => null,
                ],
            ]]);
    }

    /** @test */
    public function it_gets_values_and_labels_of_multi_select_field()
    {
        $field = [
            'type' => 'select',
            'multiple' => true,
            'options' => [
                'first' => 'Label of First',
                'second' => 'Label of Second',
            ],
        ];

        $this->createEntryWithFields([
            'one' => [
                'value' => ['second'],
                'field' => $field,
            ],
            'two' => [
                'value' => ['second', 'first'],
                'field' => $field,
            ],
            'none' => [
                'value' => null,
                'field' => $field,
            ],
        ]);

        $query = <<<'GQL'
{
    entry(id: "1") {
        ... on Entry_Test_Blueprint {
            one {
                value
                label
            }
            two {
                value
                label
            }
            none {
                value
                label
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'one' => [
                        [
                            'value' => 'second',
                            'label' => 'Label of Second',
                        ],
                    ],
                    'two' => [
                        [
                            'value' => 'second',
                            'label' => 'Label of Second',
                        ],
                        [
                            'value' => 'first',
                            'label' => 'Label of First',
                        ],
                    ],
                    'none' => null,
                ],
            ]]);
    }
}
