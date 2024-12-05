<?php

namespace Tests\StarterKits;

use Facades\Statamic\StarterKits\Hook;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HookTest extends TestCase
{
    #[Test]
    public function it_can_find_and_return_hook_instance_from_class_path()
    {
        $this->assertFalse(class_exists('StarterKitHook'));

        $hook = Hook::find(__DIR__.'/__fixtures__/StarterKitHook.php');

        $this->assertTrue(class_exists('StarterKitHook'));
        $this->assertInstanceOf('StarterKitHook', $hook);
    }

    #[Test]
    public function it_returns_null_when_hook_class_doesnt_exist()
    {
        $hook = Hook::find(__DIR__.'/__fixtures__/NonExistent.php');

        $this->assertNull($hook);
    }
}
