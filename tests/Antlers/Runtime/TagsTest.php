<?php

namespace Tests\Antlers\Runtime;

use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class TagsTest extends ParserTestCase
{
    public function test_nested_double_braces_can_be_used_to_supply_parameter_values()
    {
        (new class extends Tags
        {
            public static $handle = 'test_receives_arguments';

            public function index()
            {
                return 'Test: '.$this->params->get('test');
            }
        })::register();

        $result = $this->renderString('{{# comment {{ test }} {{ value }} #}}{{ test_receives_arguments test="{{ value }}" }}{{# value #}}{{ value }}{{# value #}} - {{ value | upper }}', [
            'value' => 'test value',
        ], true);

        $this->assertSame('Test: test valuetest value - TEST VALUE', $result);

        $result = $this->renderString('{{# comment {{ test }} {{ value }} #}}{{ test_receives_arguments test="{{ value }}" }}{{# value #}}{{ value }}{{# value #}} - ', [
            'value' => 'test value',
        ], true);

        $this->assertSame('Test: test valuetest value - ', $result);
    }

    public function test_collections_returned_from_tags()
    {
        (new class extends Tags
        {
            public static $handle = 'test_tag';

            public function index()
            {
                return collect(['a' => 'b']);
            }
        })::register();

        $this->assertSame('b', $this->renderString('{{ test_tag }}{{ a }}{{ /test_tag }}', [], true));
    }
}
