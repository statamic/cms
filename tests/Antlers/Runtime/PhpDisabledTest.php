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
}
