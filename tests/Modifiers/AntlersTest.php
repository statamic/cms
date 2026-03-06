<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\TestCase;

class AntlersTest extends TestCase
{
    public function tearDown(): void
    {
        GlobalRuntimeState::$allowedContentModifierPaths = [];
        GlobalRuntimeState::$isEvaluatingUserData = true;

        parent::tearDown();
    }

    #[Test]
    public function it_parses_as_antlers(): void
    {
        $modified = $this->modify('foo {{ foo }} bar {{ bar }}', ['foo' => 'alfa', 'bar' => 'bravo']);
        $this->assertEquals('foo alfa bar bravo', $modified);
    }

    #[Test]
    public function trusted_argument_does_not_escalate_when_current_runtime_is_untrusted(): void
    {
        GlobalRuntimeState::$isEvaluatingUserData = true;

        $this->assertSame('foo bar ', $this->modify('foo {{ foo }} {{$ "hello" $}}', ['foo' => 'bar'], ['trusted']));
    }

    #[Test]
    public function trusted_argument_parses_in_trusted_mode_when_current_runtime_is_already_trusted(): void
    {
        GlobalRuntimeState::$isEvaluatingUserData = false;

        $this->assertSame('foo bar hello', $this->modify('foo {{ foo }} {{$ "hello" $}}', ['foo' => 'bar'], ['trusted']));
    }

    private function modify($value, array $context = [], array $params = [])
    {
        return Modify::value($value)->context($context)->antlers($params)->fetch();
    }
}
