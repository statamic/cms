<?php

namespace Tests\Fields;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Stache;
use Statamic\Fields\ParsedYamlFiles;
use Statamic\Support\Str;
use Statamic\Yaml\ParseException;
use Tests\TestCase;

class ParsedYamlFilesTest extends TestCase
{
    private $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/statamic-parsed-yaml-'.uniqid();
        mkdir($this->dir);
    }

    public function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->dir);

        parent::tearDown();
    }

    #[Test]
    public function it_parses_a_file()
    {
        file_put_contents($path = $this->dir.'/test.yaml', "title: Test\nfields:\n  - handle: one\n");

        $this->assertSame(['title' => 'Test', 'fields' => [['handle' => 'one']]], ParsedYamlFiles::parse($path));
    }

    #[Test]
    public function it_reuses_what_it_parsed_while_the_file_is_unchanged()
    {
        file_put_contents($path = $this->dir.'/test.yaml', 'title: Test');

        ParsedYamlFiles::parse($path);

        // Swap what's stored for something parsing the file couldn't give, to show it's what comes back.
        $key = 'statamic.parsed-yaml::'.md5(Str::after($path, base_path()));
        Stache::cacheStore()->forever($key, ['data' => ['title' => 'From the cache']] + Stache::cacheStore()->get($key));

        $this->assertSame(['title' => 'From the cache'], ParsedYamlFiles::parse($path));
    }

    #[Test]
    public function it_parses_the_file_again_when_it_changes()
    {
        file_put_contents($path = $this->dir.'/test.yaml', 'title: Before');
        $this->assertSame(['title' => 'Before'], ParsedYamlFiles::parse($path));

        // Same length and modification time, different contents.
        $mtime = filemtime($path);
        file_put_contents($path, 'title: Behind');
        touch($path, $mtime);

        $this->assertSame(['title' => 'Behind'], ParsedYamlFiles::parse($path));
    }

    #[Test]
    public function it_doesnt_store_anything_for_a_file_that_doesnt_parse()
    {
        file_put_contents($path = $this->dir.'/test.yaml', "title: [unclosed\n");

        try {
            ParsedYamlFiles::parse($path);
            $this->fail('The file should not have parsed.');
        } catch (ParseException $e) {
            $this->assertSame($path, $e->getFile());
        }

        $this->assertNull(Stache::cacheStore()->get('statamic.parsed-yaml::'.md5(Str::after($path, base_path()))));
    }

    #[Test]
    public function an_empty_file_is_an_empty_array()
    {
        file_put_contents($path = $this->dir.'/test.yaml', '');

        $this->assertSame([], ParsedYamlFiles::parse($path));
    }
}
