<?php

namespace Tests\Yaml;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Statamic\Yaml\CannotFastParse;
use Statamic\Yaml\FastParser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * The fast parser must always give exactly the same result as Symfony's parser,
 * so these tests compare the two rather than asserting specific values.
 */
class FastParserTest extends TestCase
{
    #[Test]
    #[DataProvider('documentProvider')]
    public function it_parses_the_same_as_symfony(string $yaml)
    {
        $this->assertParsesTheSameAsSymfony($yaml);
    }

    public static function documentProvider()
    {
        return collect([
            'empty' => '',
            'only whitespace' => "  \n\n",
            'flat mapping' => "title: Hello\ncount: 3\npublished: true\n",
            'no trailing newline' => 'title: Hello',
            'front matter marker' => "---\ntitle: Hello\n",
            'nested mappings' => "seo:\n  title: Hello\n  meta:\n    robots: noindex\nafter: 1\n",
            'sequence of scalars' => "tags:\n  - one\n  - two\n",
            'unindented sequence' => "tags:\n- one\n- two\nafter: 1\n",
            'sequence of mappings' => "items:\n  -\n    type: text\n    text: Hi\n  -\n    type: image\n",
            'compact sequence items' => "items:\n  - type: text\n    text: Hi\n  - type: image\n",
            'nested sequences' => "a:\n  -\n    -\n      - deep\n",
            'empty values' => "a:\nb:\n  c:\nd: 1\n",
            'empty sequence item' => "a:\n  -\n  - x\n",
            'blank lines between keys' => "a: 1\n\n\nb:\n\n  c: 2\n",
            'trailing spaces' => "a: 1  \nb:  \n  c: 2  \n",
            'bard' => "content:\n  -\n    type: paragraph\n    attrs:\n      textAlign: left\n    content:\n      -\n        type: text\n        marks:\n          -\n            type: link\n            attrs:\n              href: 'https://example.com'\n              rel: null\n        text: 'a link'\n",

            'quoted strings' => "a: 'single'\nb: \"double\"\nc: 'it''s'\nd: \"esc\\\"aped\"\ne: \"tab\\there\"\nf: ''\ng: \"\"\n",
            'special values' => "a: null\nb: Null\nc: ~\nd: TRUE\ne: false\nf: yes\ng: no\nh: on\n",
            'numbers' => "a: 0\nb: -1\nc: 012\nd: 0x1F\ne: 1.5\nf: 1e3\ng: .inf\nh: .NaN\ni: 9223372036854775807\nj: 99999999999999999999\nk: 1_000\n",
            'dates and times' => "a: 2024-01-01\nb: 2024-01-01 10:00:00\nc: 10:30\nd: 1:30\n",
            'urls and paths' => "a: https://example.com/a?b=c\nb: images/foo.jpg\nc: foo:bar\n",
            'unicode' => "a: café\nb: 日本語\nc: 😀\n",
            'comments after values' => "a: foo #comment\nb: foo#bar\n",
            'quoted keys' => "'quoted key': 1\n\"another: key\": 2\n",
            'numeric keys' => "1: one\n2: two\n1.5: float\n",
            'reserved keys' => "true: 1\n",
            'flow collections' => "a: { }\nb: []\nc: [a, b]\nd: { a: 1, b: [2] }\n",

            'literal block' => "a: |\n  line one\n  line two\nb: 1\n",
            'literal block, strip' => "a: |-\n  line one\n  line two\nb: 1\n",
            'literal block, keep' => "a: |+\n  line one\n\nb: 1\n",
            'literal block, last in document' => "a: |\n  text\n",
            'literal block, no final newline' => "a: |\n  text",
            'literal block, nested' => "a:\n  b: |\n    text\n  c: 1\n",
            'literal block, blank lines with spaces' => "a: |-\n  one \n   \n  \n  two\n   \nb: 1\n",
            'literal block, more indented lines' => "a: |\n  one\n    indented\n  two\n",
            'literal block, in compact item' => "- name: x\n  b: |\n    text\n- name: y\n",
            'literal block, in compact item, blank line' => "- name: x\n  with:\n    b: |\n      text\n\n- name: y\n",
            'folded block' => "a: >\n  one\n  two\n",

            'comment lines' => "# comment\na: 1\n  # indented comment\nb: 2\n",
            'anchors and aliases' => "a: &anchor\n  b: 1\nc: *anchor\n",
            'merge keys' => "a: &anchor\n  b: 1\nc:\n  <<: *anchor\n  d: 2\n",
            'tags' => "a: !!str 1\nb: !custom foo\n",
            'multi-line plain scalar' => "a: one\n  two\nb: 1\n",
            'multi-line quoted scalar' => "a: 'one\n  two'\nb: 1\n",
            'multi-line flow collection' => "a: [one,\n  two]\nb: 1\n",
            'document end marker' => "a: 1\n...\n",
            'windows line endings' => "a: 1\r\nb: 2\r\n",

            'top level sequence' => "- a\n- b\n",
            'top level scalar' => 'just a string',
            'invalid: tab indentation' => "a:\n\tb: 1\n",
            'invalid: duplicate keys' => "a: 1\na: 2\n",
            'invalid: colon in plain value' => "a: foo: bar\n",
            'invalid: stray characters after flow collection' => "a: [a, b]0\n",
            'invalid: complex mapping' => "a:\n  - ? complex\n",
            'invalid: unexpected indentation' => "a: 1\n  b: 2\n",
            'invalid: mapping in sequence' => "a:\n  - x\n  b: 1\n",
            'invalid: utf-8' => "a: \xff\n",
        ])->mapWithKeys(fn ($yaml, $name) => [$name => [$yaml]])->all();
    }

    #[Test]
    public function it_uses_the_fast_path_for_content_that_statamic_writes()
    {
        $data = [
            'id' => '0a1b2c3d-4e5f-6789-abcd-ef0123456789',
            'title' => "It's a title: with a colon",
            'published' => true,
            'count' => 3,
            'nothing' => null,
            'empty' => [],
            'tags' => ['one', 'two'],
            'text' => "Line one\nLine two\n\n  indented",
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'Hello']]],
                ['type' => 'set', 'attrs' => ['id' => 'abc', 'values' => ['type' => 'image', 'image' => ['a.jpg']]]],
            ],
        ];

        $yaml = Yaml::dump($data, 100, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $this->assertSame($data, $this->parseWithoutFallingBack($yaml));
        $this->assertSame($data, $this->parseWithoutFallingBack("---\n".$yaml));
    }

    #[Test]
    public function it_parses_randomly_generated_values_the_same_as_symfony()
    {
        mt_srand(1);

        for ($i = 0; $i < 1000; $i++) {
            $value = $this->randomScalar();

            $this->assertParsesTheSameAsSymfony("k: $value\n");
            $this->assertParsesTheSameAsSymfony("k:\n  - $value\n  - x\n");
            $this->assertParsesTheSameAsSymfony("$value: x\n");
        }
    }

    #[Test]
    public function it_parses_randomly_generated_documents_the_same_as_symfony()
    {
        mt_srand(1);

        for ($i = 0; $i < 150; $i++) {
            $data = ['root' => $this->randomValue(5), 'other' => $this->randomValue(3)];

            foreach ([[100, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK], [100, 4, 0], [2, 2, 0]] as [$inline, $indent, $flags]) {
                $yaml = Yaml::dump($data, $inline, $indent, $flags);

                $this->assertParsesTheSameAsSymfony($yaml);
                $this->assertParsesTheSameAsSymfony(str_replace("\n", "\n\n", $yaml));
                $this->assertParsesTheSameAsSymfony(rtrim($yaml, "\n"));
            }
        }
    }

    private function assertParsesTheSameAsSymfony(string $yaml): void
    {
        $expected = $this->attempt(fn () => (new Parser)->parse($yaml));
        $actual = $this->attempt(fn () => (new FastParser)->parse($yaml));

        $this->assertSame($expected, $actual, 'Failed parsing: '.json_encode($yaml));
    }

    private function attempt(callable $parse): array
    {
        try {
            // Serialized, so that types must match exactly and NAN equals NAN.
            return ['result' => serialize($parse())];
        } catch (ParseException $e) {
            return ['error' => $e->getMessage(), 'line' => $e->getParsedLine()];
        } catch (\Throwable $e) {
            return ['error' => get_class($e).': '.$e->getMessage()];
        }
    }

    private function parseWithoutFallingBack(string $yaml): array
    {
        try {
            return (new ReflectionMethod(FastParser::class, 'parseBlocks'))->invoke(new FastParser, $yaml);
        } catch (CannotFastParse) {
            $this->fail('The fast parser fell back to Symfony for: '.json_encode($yaml));
        }
    }

    // Block scalar headers ("|", ">") are left out, as Symfony emits warnings for some
    // random combinations. They're covered by the document provider instead.
    private const ATOMS = [
        '0', '00', '007', '0o17', '0x1F', '-0', '+1', '1', '-1', '9223372036854775807', '99999999999999999999', '1.5', '1e3', '.5', '.inf', '-.inf', '.NaN',
        'true', 'True', 'FALSE', 'null', 'Null', '~', 'yes', 'no', 'on', 'y', '2024-01-01', '2024-01-01 10:00:00', '2024-01-01T10:00:00Z', '10:30', '1:30', '1_000',
        'foo', 'foo-bar', 'foo_bar', 'foo.bar', 'foo/bar', 'e', 'e1', 'images/foo.jpg', 'https://example.com/a?b=c', 'foo:bar', 'foo: bar', 'foo #bar', 'foo#bar', '#foo',
        "'single'", "'it''s'", "''", '""', '"double"', '"esc\\"aped"', '"uni\\u00e9"', "'a\"b'", "'unterminated", '"unterminated',
        '{ }', '[]', '[a, b]', '{ a: 1 }', '&anchor foo', '*alias', '!tag foo', '!!str 1', '? complex', '@at', '`tick', '%pct',
        'café', '日本語', 'with space', '-', '- x', '---', '...', ':', 'a:', ',', '<<', '\\', 'v1.2.3', '-foo', '.foo', '_foo',
    ];

    private function randomScalar(): string
    {
        $atom = self::ATOMS[mt_rand(0, count(self::ATOMS) - 1)];

        // Sometimes join two together to make stranger combinations.
        return mt_rand(0, 4) === 0 ? $atom.self::ATOMS[mt_rand(0, count(self::ATOMS) - 1)] : $atom;
    }

    private function randomValue(int $depth): mixed
    {
        $r = mt_rand(0, 9);

        if ($depth > 0 && $r < 3) {
            return array_map(fn () => $this->randomValue($depth - 1), range(1, mt_rand(1, 4)));
        }

        if ($depth > 0 && $r < 6) {
            $map = [];

            for ($i = mt_rand(1, 4); $i > 0; $i--) {
                $map[preg_replace('/\s/', '_', $this->randomScalar())] = $this->randomValue($depth - 1);
            }

            return $map;
        }

        return match (mt_rand(0, 8)) {
            0 => mt_rand(-1000, 1000),
            1 => (bool) mt_rand(0, 1),
            2 => null,
            3 => mt_rand() / 7,
            4 => "line one\nline two".(mt_rand(0, 1) ? "\n" : ''),
            5 => "a \n\n \n\nb",
            6 => "  indented first\nsecond\n\n",
            7 => '',
            default => $this->randomScalar(),
        };
    }
}
