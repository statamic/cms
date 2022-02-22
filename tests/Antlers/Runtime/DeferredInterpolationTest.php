<?php

namespace Tests\Antlers\Runtime;

use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class DeferredInterpolationTest extends ParserTestCase
{
    public function test_interpolated_regions_do_not_get_called_too_early()
    {
        $instance = (new class extends Tags
        {
            public static $handle = 'state';
            public static $testIncrement = 0;

            public function index()
            {
                return 'hi!';
            }

            public function test()
            {
                self::$testIncrement += 1;

                return 'test'.self::$testIncrement;
            }
        });

        $instance::register();

        $template = <<<'EOT'
{{ (true) ? {
    {state:test}
 } : {state:test} }}
EOT;
        $this->renderString($template, [], true);
        $this->assertSame(1, $instance::$testIncrement);
    }

    public function test_interpolated_regions_do_not_get_called_too_early_with_else_branch_and_whitepsace()
    {
        $instance = (new class extends Tags
        {
            public static $handle = 'state';
            public static $testIncrement = 0;

            public function index()
            {
                return 'hi!';
            }

            public function test()
            {
                self::$testIncrement += 1;

                return 'test'.self::$testIncrement;
            }
        });

        $instance::register();

        $template = <<<'EOT'
{{ (false) ? {
    {state:test}
 } : { state:test } }}
EOT;
        $this->renderString($template, [], true);
        $this->assertSame(1, $instance::$testIncrement);
    }

    public function test_interpolated_regions_do_not_get_called_too_early_with_logic_group_syntax()
    {
        $instance = (new class extends Tags
        {
            public static $handle = 'state';
            public static $testIncrement = 0;

            public function index()
            {
                return 'hi!';
            }

            public function test()
            {
                self::$testIncrement += 1;

                return 'test'.self::$testIncrement;
            }
        });

        $instance::register();

        $template = <<<'EOT'
{{ (true) ? (
    {state:test}
 ) : (
    {state:test}
   ) }}
EOT;
        $this->renderString($template, [], true);
        $this->assertSame(1, $instance::$testIncrement);
    }

    public function test_interpolated_regions_do_not_get_called_too_early_with_else_branch_and_whitepsace_with_logic_group_syntax()
    {
        $instance = (new class extends Tags
        {
            public static $handle = 'state';
            public static $testIncrement = 0;

            public function index()
            {
                return 'hi!';
            }

            public function test()
            {
                self::$testIncrement += 1;

                return 'test'.self::$testIncrement;
            }
        });

        $instance::register();

        $template = <<<'EOT'
{{ (false) ? (
    {state:test}
 ) : ( { state:test } ) }}
EOT;
        $this->renderString($template, [], true);
        $this->assertSame(1, $instance::$testIncrement);
    }
}
