<?php

namespace Tests\Support;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Support\Traits\Hookable;
use Tests\TestCase;
use Tests\TestDependency;

class HookableTest extends TestCase
{
    #[Test]
    public function hooks_can_be_run()
    {
        $test = $this;

        TestHookable::hook('constructed', function ($payload, $next) use ($test) {
            $test->assertEquals('initial', $payload);
            $test->assertInstanceOf(TestHookable::class, $this);
            $this->setFoo('bar');

            return $next($payload);
        });

        // Do it twice to ensure that they are executed in order
        // and the payload is passed along through the closures.
        TestHookable::hook('constructed', function ($payload, $next) use ($test) {
            $test->assertInstanceOf(TestHookable::class, $this);
            $test->assertEquals('initial', $payload);
            $this->setFoo($this->foo.'baz');

            return $next($payload);
        });

        $class = app(TestHookable::class);

        $this->assertEquals('barbaz', $class->foo);
    }

    #[Test]
    public function hooks_from_one_class_dont_happen_on_another()
    {
        $hooksRan = 0;

        TestHookable::hook('constructed', function ($payload, $next) use (&$hooksRan) {
            $this->setFoo('bar');
            $hooksRan++;

            return $next($this);
        });

        AnotherTestHookable::hook('constructed', function ($payload, $next) use (&$hooksRan) {
            $this->setFoo($this->foo.'baz');
            $hooksRan++;

            return $next($this);
        });

        $class = app(AnotherTestHookable::class);

        $this->assertEquals(1, $hooksRan);
        $this->assertEquals('baz', $class->foo);
    }
}

class TestHookable
{
    use Hookable;

    public $dependency;
    public $foo = 'initial';

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;

        $this->runHooks('constructed', $this->foo);
    }

    public function setFoo(string $foo)
    {
        $this->foo = $foo;
    }
}

class AnotherTestHookable
{
    use Hookable;
    public $foo = '';

    public function __construct()
    {
        $this->runHooks('constructed');
    }

    public function setFoo(string $foo)
    {
        $this->foo = $foo;
    }
}
