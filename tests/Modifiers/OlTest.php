<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class OlTest extends TestCase
{
    #[Test]
    public function it_turns_an_array_into_an_html_ordered_list(): void
    {
        $input = [
            'sushi',
            'broccoli',
            'kale',
        ];
        $expected = '<ol><li>sushi</li><li>broccoli</li><li>kale</li></ol>';
        $modified = $this->modify($input, []);
        $this->assertEquals($expected, $modified);
    }

    #[Test]
    public function it_turns_an_empty_yaml_key_value_mapping_into_empty_string(): void
    {
        $input = [];

        $expected = '';
        $modified = $this->modify($input, []);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->ol($params)->fetch();
    }
}
