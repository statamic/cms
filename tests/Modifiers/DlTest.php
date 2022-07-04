<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class DlTest extends TestCase
{
    /** @test */
    public function it_turns_an_yaml_key_value_mapping_into_html_definition_list(): void
    {
        $input = [
            'Delicious' => [
                'bacon',
                'sushi',
            ],
            'Green' => [
                'broccoli',
                'kale',
            ],
        ];

        $expected = '<dl><dt>Delicious</dt><dd>bacon</dd><dd>sushi</dd><dt>Green</dt><dd>broccoli</dd><dd>kale</dd></dl>';
        $modified = $this->modify($input, []);
        $this->assertEquals($expected, $modified);
    }

    /** @test */
    public function it_turns_an_empty_yaml_key_value_mapping_into_html_definition_list(): void
    {
        $input = [];

        $expected = '<dl></dl>';
        $modified = $this->modify($input, []);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->dl($params)->fetch();
    }
}
