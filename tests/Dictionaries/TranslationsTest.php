<?php

namespace Tests\Dictionaries;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TranslationsTest extends TestCase
{
    #[Test]
    public function translated_names_do_not_mix_alphabets_in_a_word()
    {
        $invalid = [];

        foreach (glob(__DIR__.'/../../lang/*/dictionary-*.php') as $file) {
            $locale = basename(dirname($file));

            $names = require $file;

            array_walk_recursive($names, function ($name, $key) use ($locale, &$invalid) {
                foreach (preg_split('/[^\p{L}]+/u', $name, -1, PREG_SPLIT_NO_EMPTY) as $word) {
                    $alphabets = preg_match('/\p{sc=Latin}/u', $word)
                        + preg_match('/\p{sc=Cyrillic}/u', $word)
                        + preg_match('/\p{sc=Greek}/u', $word);

                    if ($alphabets > 1) {
                        $invalid[] = "{$locale} {$key}: {$name}";
                    }
                }
            });
        }

        $this->assertSame([], $invalid);
    }
}
