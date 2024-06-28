<?php

namespace Tests\Preferences;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Preference;
use Tests\TestCase;

class DefaultPreferencesTest extends TestCase
{
    private $files;

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

    #[Test]
    public function it_gets_empty_array_by_default()
    {
        $this->assertFileDoesNotExist(resource_path('preferences.yaml'));
        $this->assertEquals([], Preference::default()->all());
    }

    #[Test]
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

        $this->assertEquals($expected, Preference::default()->all());
    }

    #[Test]
    public function it_gets_a_preference_by_key()
    {
        Preference::default()->set([
            'foo' => 'bar',
            'bar' => 'baz',
        ])->save();

        $this->assertEquals('bar', Preference::default()->get('foo'));
    }

    #[Test]
    public function it_removes_a_preference_by_key()
    {
        Preference::default()->set([
            'foo' => 'bar',
            'bar' => 'baz',
        ])->save();

        Preference::default()->remove('foo')->save();

        $this->assertEquals(['bar' => 'baz'], Preference::default()->all());
    }

    #[Test]
    public function it_saves_preferences_to_file()
    {
        $this->assertFileDoesNotExist(resource_path('preferences.yaml'));

        Preference::default()->set($preferences = [
            'collections' => [
                'posts' => [
                    'columns' => [
                        'title',
                        'slug',
                        'audio',
                    ],
                ],
            ],
        ])->save();

        $this->assertFileExists(resource_path('preferences.yaml'));

        $this->assertEquals($preferences, Preference::default()->all());
    }

    #[Test]
    public function it_merges_preferences_to_file()
    {
        $this->assertFileDoesNotExist(resource_path('preferences.yaml'));

        Preference::default()->set($preferences = [
            'foo' => 'bar',
            'bar' => 'baz',
        ])->save();

        $this->assertFileExists(resource_path('preferences.yaml'));

        $this->assertEquals($preferences, Preference::default()->all());

        Preference::default()->merge($preferences = [
            'foo' => 'qux',
        ])->save();

        $expected = [
            'foo' => 'qux',
            'bar' => 'baz',
        ];

        $this->assertEquals($expected, Preference::default()->all());
    }

    #[Test]
    public function it_sets_a_single_preference_key()
    {
        Preference::default()->set([
            'foo' => 'bar',
            'bar' => 'baz',
        ])->save();

        Preference::default()->set('foo', 'qux')->save();

        $expected = [
            'foo' => 'qux',
            'bar' => 'baz',
        ];

        $this->assertEquals($expected, Preference::default()->all());
    }

    private function cleanup()
    {
        if ($this->files->exists($path = resource_path('preferences.yaml'))) {
            $this->files->delete($path);
        }
    }
}
