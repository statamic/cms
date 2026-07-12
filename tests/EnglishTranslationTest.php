<?php

namespace Tests;

class EnglishTranslationTest extends TestCase
{
    public function test_english_json_file_exists()
    {
        $this->assertFileExists($this->langPath('en.json'));
    }

    public function test_english_json_is_valid()
    {
        $translations = $this->englishTranslations();

        $this->assertIsArray($translations);
        $this->assertGreaterThan(0, count($translations));
    }

    public function test_english_json_translations_are_symmetric()
    {
        foreach ($this->englishTranslations() as $key => $value) {
            $this->assertEquals($key, $value);
        }
    }

    public function test_english_json_covers_the_keys_shared_by_all_other_locales()
    {
        $english = array_keys($this->englishTranslations());

        $localeKeys = collect(glob($this->langPath('*.json')))
            ->reject(fn ($path) => basename($path) === 'en.json')
            ->map(fn ($path) => array_keys(json_decode(file_get_contents($path), true)))
            ->all();

        $shared = collect(array_intersect(...$localeKeys));

        $missing = $shared->diff($english);

        $this->assertEmpty($missing, 'en.json is missing keys present in every other locale: '.$missing->implode(', '));
    }

    private function englishTranslations(): array
    {
        return json_decode(file_get_contents($this->langPath('en.json')), true);
    }

    private function langPath(string $file): string
    {
        return __DIR__.'/../lang/'.$file;
    }
}
