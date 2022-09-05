<?php

namespace Tests\Preferences;

use Illuminate\Filesystem\Filesystem;
use Statamic\Preferences\DefaultPreferences;
use Tests\TestCase;

class DefaultPreferencesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->cleanup();
    }

    public function tearDown(): void
    {
        $this->cleanup();

        parent::tearDown();
    }

    /** @test */
    public function it_gets_empty_array_by_default()
    {
        $this->assertFileNotExists(resource_path('preferences.yaml'));
        $this->assertEquals([], DefaultPreferences::all());
    }

    /** @test */
    public function it_gets_preferences_from_yaml()
    {
        $this->files->put(resource_path('preferences.yaml'), <<<'EOT'
collections:
  posts:
    columns:
      - title
      - slug
      - audio
EOT
        );

        $expected = [
            'collections' => [
                'posts' => [
                    'columns' => [
                        'title',
                        'slug',
                        'audio',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, DefaultPreferences::all());
    }

    /** @test */
    public function it_saves_preferences_to_file()
    {
        $this->assertFileNotExists(resource_path('preferences.yaml'));

        DefaultPreferences::save($preferences = [
            'collections' => [
                'posts' => [
                    'columns' => [
                        'title',
                        'slug',
                        'audio',
                    ],
                ],
            ],
        ]);

        $this->assertFileExists(resource_path('preferences.yaml'));

        $this->assertEquals($preferences, DefaultPreferences::all());
    }

    /** @test */
    public function it_merges_preferences_to_file()
    {
        $this->assertFileNotExists(resource_path('preferences.yaml'));

        DefaultPreferences::save($preferences = [
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->assertFileExists(resource_path('preferences.yaml'));

        $this->assertEquals($preferences, DefaultPreferences::all());

        DefaultPreferences::save($preferences = [
            'foo' => 'qux',
        ]);

        $expected = [
            'foo' => 'qux',
            'bar' => 'baz',
        ];

        $this->assertEquals($expected, DefaultPreferences::all());
    }

    private function cleanup()
    {
        if ($this->files->exists($path = resource_path('preferences.yaml'))) {
            $this->files->delete($path);
        }
    }
}
