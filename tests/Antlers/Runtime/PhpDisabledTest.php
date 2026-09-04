<?php

namespace Tests\Antlers\Runtime;

use Statamic\Facades\Antlers;
use Tests\TestCase;

class PhpDisabledTest extends TestCase
{
    public function test_it_ignores_inline_php_blocks_when_disabled()
    {
        $result = (string) Antlers::parse('Before {{? echo "hello"; ?}} After', [], false);

        $this->assertSame('Before  After', $result);
    }

    public function test_it_ignores_inline_echo_blocks_when_disabled()
    {
        $result = (string) Antlers::parse('Before {{$ "hello" $}} After', []);

        $this->assertSame('Before  After', $result);
    }

    public function test_php_disabled_is_the_default()
    {
        $result = (string) Antlers::parse('Before {{? echo "hello"; ?}} After', []);

        $this->assertSame('Before  After', $result);
    }

    public function test_inline_php_tags_disabled_is_the_default()
    {
        $result = (string) Antlers::parse('Before <?php echo "hello"; ?> After', []);

        $this->assertSame('Before &lt;?php echo "hello"; ?> After', $result);
    }

    public function test_php_nodes_inside_interpolated_parameters_are_not_evaluated_when_disabled()
    {
        (new class extends \Statamic\Tags\Tags
        {
            protected static $handle = 'php_param_echo';

            public function index()
            {
                return '['.$this->params->get('value').']';
            }
        })::register();

        $data = ['title' => 'The Title', 'items' => ['a', 'b']];
        $template = '{{ php_param_echo value="{{$ strtoupper($title) $}}" }}';
        $loop = '{{ items }}<li>{{ php_param_echo value="{{ value }}:{{$ strtoupper($value) $}}" }}</li>{{ /items }}';

        $this->assertSame('[THE TITLE]', (string) Antlers::parse($template, $data, true));
        $this->assertSame('<li>[a:A]</li><li>[b:B]</li>', (string) Antlers::parse($loop, $data, true));

        $this->assertSame('', (string) Antlers::parse($template, $data, false));
        $this->assertSame('', (string) Antlers::parse($template, $data));
        $this->assertSame('<li></li><li></li>', (string) Antlers::parse($loop, $data, false));
        $this->assertSame('', (string) Antlers::parse('{{ php_param_echo value="a{{? $x = 1; ?}}b" }}', $data, false));
    }

    public function test_it_allows_inline_echo_blocks_when_enabled()
    {
        $result = (string) Antlers::parse('Before {{$ "hello" $}} After', [], true);

        $this->assertSame('Before hello After', $result);
    }

    public function test_it_allow_inline_php_blocks_when_enabled()
    {
        $result = (string) Antlers::parse('Before {{? echo "hello"; ?}} After', [], true);

        $this->assertSame('Before hello After', $result);
    }

    public function test_method_calls_are_not_evaluated_when_php_is_disabled()
    {
        $helper = new class()
        {
            public $wasCalled = false;

            public function mutate()
            {
                $this->wasCalled = true;

                return 'changed';
            }
        };

        $result = (string) Antlers::parse('{{ helper:mutate() }}', [
            'helper' => $helper,
        ], false);

        $this->assertSame('', $result);
        $this->assertFalse($helper->wasCalled);
    }

    public function test_method_calls_are_evaluated_when_php_is_enabled()
    {
        $helper = new class()
        {
            public $wasCalled = false;

            public function mutate()
            {
                $this->wasCalled = true;

                return 'changed';
            }
        };

        $result = (string) Antlers::parse('{{ helper:mutate() }}', [
            'helper' => $helper,
        ], true);

        $this->assertSame('changed', $result);
        $this->assertTrue($helper->wasCalled);
    }

    public function test_strict_variable_method_calls_are_not_evaluated_when_php_is_disabled()
    {
        $helper = new class()
        {
            public $wasCalled = false;

            public function mutate()
            {
                $this->wasCalled = true;

                return 'changed';
            }
        };

        $result = (string) Antlers::parse('{{ $helper->mutate() }}', [
            'helper' => $helper,
        ], false);

        $this->assertSame('', $result);
        $this->assertFalse($helper->wasCalled);
    }

    public function test_strict_variable_method_calls_are_evaluated_when_php_is_enabled()
    {
        $helper = new class()
        {
            public $wasCalled = false;

            public function mutate()
            {
                $this->wasCalled = true;

                return 'changed';
            }
        };

        $result = (string) Antlers::parse('{{ $helper->mutate() }}', [
            'helper' => $helper,
        ], true);

        $this->assertSame('changed', $result);
        $this->assertTrue($helper->wasCalled);
    }
}
