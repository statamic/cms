<?php

namespace Tests\View\Blade;

use Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $blade;

    public function setUp(): void
    {
        parent::setUp();

        $this->blade = app('blade.compiler');
    }

    // https://stevegrunwell.com/blog/custom-laravel-blade-directives/
    protected function assertDirectiveOutput($expected, $expression, $variables = [], $message = '')
    {
        $compiled = $this->blade->compileString($expression);

        ob_start();
        extract($variables);
        eval(' ?>'.$compiled.'<?php ');

        $output = ob_get_clean();

        $this->assertSame($expected, $output, $message);
    }
}
