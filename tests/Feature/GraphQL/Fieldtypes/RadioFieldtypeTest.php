<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class RadioFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_values_and_labels()
    {
        $field = [
            'type' => 'radio',
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
}
