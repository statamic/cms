<?php

namespace Tests\Antlers\Parser;

use PHPUnit\Framework\TestCase;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;

class CharacterOffsetsTest extends TestCase
{
    // a(0) é(1-2) 日(3-5) 🎉(6-9) b(10); 5 characters, 11 bytes.
    const MIXED = "a\u{00E9}\u{65E5}\u{1F389}b";

    public function test_ascii_offsets_map_to_themselves_and_clamp_to_the_length()
    {
        $this->assertSame([0 => 0, 3 => 3, 5 => 5, 9 => 5], CharacterOffsets::toBytes('hello', [0, 3, 5, 9]));
        $this->assertSame([0 => 0, 3 => 3, 5 => 5, 9 => 5], CharacterOffsets::toCharacters('hello', [0, 3, 5, 9]));
        $this->assertSame([], CharacterOffsets::toBytes('hello', []));
        $this->assertSame([], CharacterOffsets::toCharacters('hello', []));
    }

    public function test_character_offsets_map_to_lead_bytes()
    {
        $this->assertSame(
            [0 => 0, 1 => 1, 2 => 3, 3 => 6, 4 => 10, 5 => 11],
            CharacterOffsets::toBytes(self::MIXED, [0, 1, 2, 3, 4, 5])
        );
    }

    public function test_byte_offsets_map_to_the_containing_character()
    {
        $this->assertSame(
            [0 => 0, 1 => 1, 2 => 1, 3 => 2, 5 => 2, 6 => 3, 9 => 3, 10 => 4, 11 => 5],
            CharacterOffsets::toCharacters(self::MIXED, [0, 1, 2, 3, 5, 6, 9, 10, 11])
        );
    }

    public function test_offsets_past_the_end_map_to_the_length()
    {
        $this->assertSame([7 => 11, 40 => 11], CharacterOffsets::toBytes(self::MIXED, [7, 40]));
        $this->assertSame([12 => 5, 40 => 5], CharacterOffsets::toCharacters(self::MIXED, [12, 40]));
    }

    public function test_input_order_and_duplicates_do_not_matter()
    {
        $expected = [4 => 10, 0 => 0, 2 => 3];

        $this->assertEquals($expected, CharacterOffsets::toBytes(self::MIXED, [4, 0, 2, 4, 0]));
        $this->assertEquals([10 => 4, 0 => 0, 3 => 2], CharacterOffsets::toCharacters(self::MIXED, [10, 0, 3, 10]));
    }

    public function test_negative_character_offsets_map_to_the_start()
    {
        $this->assertSame([-2 => 0, 0 => 0, 1 => 1], CharacterOffsets::toBytes(self::MIXED, [-2, 0, 1]));
    }

    public function test_large_advances_are_split_into_steps()
    {
        $source = str_repeat("\u{00E9}", 40000).str_repeat('x', 100);

        $this->assertSame(
            [1 => 2, 32768 => 65536, 39999 => 79998, 40000 => 80000, 40050 => 80050, 50000 => 80100],
            CharacterOffsets::toBytes($source, [1, 32768, 39999, 40000, 40050, 50000])
        );
    }

    public function test_byte_offsets_can_be_counted_from_a_known_anchor()
    {
        // Anchor: byte 6 is character 3 (the emoji). Offsets before it still count from the start.
        $this->assertSame(
            [1 => 1, 6 => 3, 10 => 4, 11 => 5],
            CharacterOffsets::toCharacters(self::MIXED, [1, 6, 10, 11], true, 6, 3)
        );

        // A wrong anchor is trusted, which is what makes it an anchor.
        $this->assertSame([10 => 41], CharacterOffsets::toCharacters(self::MIXED, [10], true, 6, 40));
    }

    public function test_conversions_round_trip_on_mixed_content()
    {
        $source = str_repeat("plain ascii line\n", 40).str_repeat("\u{65E5}\u{672C}\u{8A9E} \u{1F389} caf\u{00E9}\n", 40);
        $characters = range(0, mb_strlen($source), 7);

        $bytes = CharacterOffsets::toBytes($source, $characters);
        $back = CharacterOffsets::toCharacters($source, array_values($bytes));

        foreach ($characters as $character) {
            $this->assertSame(strlen(mb_substr($source, 0, $character)), $bytes[$character], "byte offset of character {$character}");
            $this->assertSame($character, $back[$bytes[$character]], "round trip of character {$character}");
        }
    }

    public function test_invalid_utf8_counts_lead_bytes_without_failing()
    {
        // A stray continuation byte attaches to nothing, so "a" is character 0.
        $this->assertSame([0 => 1, 1 => 2, 2 => 4], CharacterOffsets::toBytes("\xA9a\u{00E9}", [0, 1, 2]));
        $this->assertSame([0 => 0, 1 => 0, 2 => 1, 4 => 2], CharacterOffsets::toCharacters("\xA9a\u{00E9}", [0, 1, 2, 4]));

        // A truncated sequence still counts as one character.
        $this->assertSame([0 => 0, 1 => 2, 2 => 3], CharacterOffsets::toBytes("\xE6\x97{{", [0, 1, 2]));
        $this->assertSame([0 => 0, 2 => 1, 3 => 2], CharacterOffsets::toCharacters("\xE6\x97{{", [0, 2, 3]));
    }
}
