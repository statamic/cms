<?php

namespace Tests\View\Blade;

class DirectivesTest extends TestCase
{
    /** @test */
    public function does_display_correctly()
    {
        $blade = "@collection('foo')";
        $expected = "<?php foreach (Statamic\Facades\Blade::collection('foo') as \$entry) { ?>";

        $this->assertSame($expected, $this->blade->compileString($blade));
    }
}
