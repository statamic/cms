<?php

namespace Tests\View\Blade;

class DirectivesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function combines_two_views()
    {
        $blade = "@collection('foo')";
        $expected = "<?php foreach (Statamic\Facades\Collection::find('foo')->queryEntries()->get()->toAugmentedArray() as \$entry) { ?>";

        $this->assertSame($expected, $this->blade->compileString($blade));
    }
}
