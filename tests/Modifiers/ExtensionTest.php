<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ExtensionTest extends TestCase
{
    public static function filenamesProvider(): array
    {
        return [
            ['pdf', 'example.pdf'],
            ['png', 'cats.png'],
            ['bat', 'autoexec.bat'],
            ['gif', 'cool_runnings.gif'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider filenamesProvider
     */
    public function it_returns_the_extension_of_filename($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->extension()->fetch();
    }
}
