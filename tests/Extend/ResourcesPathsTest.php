<?php

namespace Statamic\Testing\Extend;

use Illuminate\Support\Facades\Request;
use Statamic\Statamic;
use Tests\TestCase;

class ResourcesPathsTest extends TestCase
{
    /** @test */
    public function scripts_can_be_created_without_version()
    {
        Statamic::script('test', 'test');

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test', $allScripts);

        $testScripts = $allScripts['test'];

        $this->assertIsArray($testScripts);
        $this->assertContains('test.js', $testScripts);
    }

    /** @test */
    public function styles_can_be_created_without_version()
    {
        Statamic::style('test', 'test');

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test', $allStyles);

        $testStyles = $allStyles['test'];

        $this->assertIsArray($testStyles);
        $this->assertContains('test.css', $testStyles);
    }

    /** @test */
    public function scripts_can_be_created_with_version()
    {
        Statamic::script('test', 'test');
        Statamic::script('test', 'versioned', '2.1.0');

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test', $allScripts);

        $testScripts = $allScripts['test'];

        $this->assertIsArray($testScripts);
        $this->assertContains('test.js', $testScripts);
        $this->assertContains('versioned.js?v=2.1.0', $testScripts);
    }

    /** @test */
    public function styles_can_be_created_with_version()
    {
        Statamic::style('test', 'test');
        Statamic::style('test', 'versioned', '2.1.0');

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test', $allStyles);

        $testStyles = $allStyles['test'];

        $this->assertIsArray($testStyles);
        $this->assertContains('test.css', $testStyles);
        $this->assertContains('versioned.css?v=2.1.0', $testStyles);
    }

    /** @test */
    public function scripts_cannot_inject_html()
    {
        Statamic::script('test', 'test', '"><script></script><script src="');

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test', $allScripts);

        $testScripts = $allScripts['test'];

        $this->assertIsArray($testScripts);
        $this->assertNotContains('test.js?v='.'"><script></script><script src="', $testScripts);
    }

    /** @test */
    public function styles_cannot_inject_html()
    {
        Statamic::style('test', 'test', '"><script></script><link href="');

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test', $allStyles);

        $testStyles = $allStyles['test'];

        $this->assertIsArray($testStyles);
        $this->assertNotContains('test.css?v='.'"><script></script><link href="', $testStyles);
    }

    /** @test */
    public function scripts_can_supply_custom_parameters()
    {
        Statamic::script('test', 'paramtest', ['v' => '3.1.0', 'key' => 'apikey']);
        Statamic::script('test', 'collecttest', collect(['v' => '3.1.0', 'key' => 'apikey', 'name' => 'test']));

        $allScripts = Statamic::availableScripts(Request::create('/'));

        $this->assertArrayHasKey('test', $allScripts);

        $testScripts = $allScripts['test'];

        $this->assertIsArray($testScripts);
        $this->assertContains('paramtest.js?v=3.1.0&key=apikey', $testScripts);
        $this->assertContains('collecttest.js?v=3.1.0&key=apikey&name=test', $testScripts);
    }

    /** @test */
    public function styles_can_supply_custom_parameters()
    {
        Statamic::style('test', 'paramtest', ['v' => '3.1.0', 'key' => 'apikey']);
        Statamic::style('test', 'collecttest', collect(['v' => '3.1.0', 'key' => 'apikey', 'name' => 'test']));

        $allStyles = Statamic::availableStyles(Request::create('/'));

        $this->assertArrayHasKey('test', $allStyles);

        $testStyles = $allStyles['test'];

        $this->assertIsArray($testStyles);
        $this->assertContains('paramtest.css?v=3.1.0&key=apikey', $testStyles);
        $this->assertContains('collecttest.css?v=3.1.0&key=apikey&name=test', $testStyles);
    }
}
