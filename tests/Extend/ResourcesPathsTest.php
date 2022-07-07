<?php

namespace Statamic\Testing\Extend;

use Illuminate\Support\Facades\Request;
use Statamic\Statamic;
use Statamic\Support\Str;
use Tests\TestCase;

class ResourcesPathsTest extends TestCase
{
    /** @test */
    public function scripts_will_automatically_be_versioned()
    {
        Statamic::script('test-a', 'test');

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test-a', $allScripts);

        $testScript = $allScripts['test-a'][0];

        $this->assertTrue(Str::startsWith($testScript, 'test.js?v='));
        // Check if the version is 16 characters long.
        $this->assertEquals(16, strlen(Str::of($testScript)->after('.js?v=')));
    }

    /** @test */
    public function styles_will_automatically_be_versioned()
    {
        Statamic::style('test-b', 'test');

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test-b', $allStyles);

        $testStyle = $allStyles['test-b'][0];

        $this->assertTrue(Str::startsWith($testStyle, 'test.css?v='));
        // Check if the version is 16 characters long.
        $this->assertEquals(16, strlen(Str::of($testStyle)->after('.css?v=')));
    }

    /** @test */
    public function scripts_can_be_passed_with_a_laravel_mix_version()
    {
        $path = 'test.js?id=some-random-laravel-mix-version';

        // We can't test the mix helper, so we emulate it by adding `?id=`, as this is
        // the versioning syntax provied by Laravel Mix.
        // Statamic::script('test', mix('your-path'));

        Statamic::script('test-c', $path);

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test-c', $allScripts);

        $testScript = $allScripts['test-c'][0];

        $this->assertEquals($testScript, $path);
    }

    /** @test */
    public function styles_can_be_passed_with_a_laravel_mix_version()
    {
        $path = 'test.css?id=some-random-laravel-mix-version';

        // We can't test the mix helper, so we emulate it by adding `?id=`, as this is
        // the versioning syntax provied by Laravel Mix.
        // Statamic::script('test', mix('your-path'));

        Statamic::style('test-d', $path);

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test-d', $allStyles);

        $testStyle = $allStyles['test-d'][0];

        $this->assertEquals($testStyle, $path);
    }
}
