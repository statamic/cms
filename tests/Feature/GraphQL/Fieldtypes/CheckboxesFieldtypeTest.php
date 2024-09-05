<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class CheckboxesFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_values_and_labels()
    {
        $field = [
            'type' => 'checkboxes',
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
