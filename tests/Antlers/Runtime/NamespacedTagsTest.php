<?php

namespace Tests\Antlers\Runtime;

use Statamic\Statamic;
use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class NamespacedTagsTest extends ParserTestCase
{
    public function test_namespaced_tag_with_method_can_be_rendered()
    {
        $this->assertSame('hi', $this->renderString('{{ acme::ns_greet:hello }}', [], true));
    }

    public function test_namespaced_tag_without_method_calls_index()
    {
        $this->assertSame('greetings', $this->renderString('{{ acme::ns_greet }}', [], true));
    }

    public function test_namespaced_tag_can_be_paired()
    {
        $this->assertSame('ab', $this->renderString('{{ acme::ns_items }}{{ value }}{{ /acme::ns_items }}', [], true));
    }

    public function test_namespaced_tag_can_be_self_closing()
    {
        $this->assertSame('greetings', $this->renderString('{{ acme::ns_greet /}}', [], true));
    }

    public function test_namespaced_alias_can_be_rendered()
    {
        $this->assertSame('hi', $this->renderString('{{ acme::ns_hi:hello }}', [], true));
    }

    public function test_bare_handle_is_not_registered_for_namespaced_tags()
    {
        $this->assertSame('', $this->renderString('{{ ns_greet:hello }}', [], true));
        $this->assertSame('', $this->renderString('{{ ns_hi:hello }}', [], true));
    }

    public function test_double_colons_in_method_part_route_to_wildcard()
    {
        $this->assertSame('wildcard: foo::bar', $this->renderString('{{ acme::ns_greet:foo::bar }}', [], true));
    }

    public function test_namespaced_tag_can_be_used_in_conditions()
    {
        $this->assertSame('yes', $this->renderString('{{ if {acme::ns_greet:hello} == "hi" }}yes{{ /if }}', [], true));
    }

    public function test_unregistered_namespace_falls_back_to_variable()
    {
        $this->assertSame('', $this->renderString('{{ unknown::ns_greet }}', [], true));
    }

    public function test_namespaced_tag_receives_namespaced_tag_and_method_properties()
    {
        $this->assertSame('acme::ns_greet:details|details', $this->renderString('{{ acme::ns_greet:details }}', [], true));
    }

    public function test_namespaced_tag_can_be_resolved_fluently()
    {
        $this->assertSame('hi', (string) Statamic::tag('acme::ns_greet:hello'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        (new class extends Tags
        {
            public static $handle = 'ns_greet';

            protected static $aliases = ['ns_hi'];

            public function index()
            {
                return 'greetings';
            }

            public function hello()
            {
                return 'hi';
            }

            public function details()
            {
                return $this->tag.'|'.$this->method;
            }

            public function wildcard($method)
            {
                return 'wildcard: '.$method;
            }
        })::register('acme');

        (new class extends Tags
        {
            public static $handle = 'ns_items';

            public function index()
            {
                return [['value' => 'a'], ['value' => 'b']];
            }
        })::register('acme');
    }
}
