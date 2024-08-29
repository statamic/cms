<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class DeslugifyTest extends TestCase
{
    public static function slackingSlugsProvider(): array
    {
        return [
            'with_dashes' => ['Just Because I Can', 'Just-Because-I-Can'],
            'with_underscores_chars' => ['Just Because I Can', 'Just_Because_I_Can'],
        ];
    }

    #[Test]
    #[DataProvider('slackingSlugsProvider')]
    public function it_replaces_all_hyphens_and_underscores_with_spaces($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->deslugify()->fetch();
    }
}
