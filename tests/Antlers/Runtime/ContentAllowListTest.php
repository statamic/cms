<?php

namespace Tests\Antlers\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Text;
use Statamic\Tags\Loader;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Tests\Antlers\ParserTestCase;

class ContentAllowListTest extends ParserTestCase
{
    public function tearDown(): void
    {
        GlobalRuntimeState::$allowedContentTagPaths = [];
        GlobalRuntimeState::$allowedContentModifierPaths = [];
        GlobalRuntimeState::$bannedContentTagPaths = [];
        GlobalRuntimeState::$bannedContentModifierPaths = [];
        GlobalRuntimeState::$bannedTagPaths = [];
        GlobalRuntimeState::$bannedModifierPaths = [];
        GlobalRuntimeState::$throwErrorOnAccessViolation = false;
        GlobalRuntimeState::$isEvaluatingUserData = false;

        parent::tearDown();
    }

    #[Test]
    public function allowed_modifier_can_be_used_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];

        $value = $this->makeAntlersTextValue('{{ title | upper }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'hello',
        ], true, true);

        $this->assertSame('HELLO', $result);
    }

    #[Test]
    public function disallowed_modifier_is_blocked_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];

        $value = $this->makeAntlersTextValue('{{ title | lower }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'HELLO',
        ], true, true);

        $this->assertSame('HELLO', $result);
    }

    #[Test]
    public function empty_modifier_allow_list_blocks_all_modifiers_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = [];

        $value = $this->makeAntlersTextValue('{{ title | upper }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'hello',
        ], true, true);

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function modifier_block_list_overrides_modifier_allow_list_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];
        GlobalRuntimeState::$bannedContentModifierPaths = ['upper'];

        $value = $this->makeAntlersTextValue('{{ title | upper }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'hello',
        ], true, true);

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function disallowed_modifier_throws_when_access_violations_are_enabled()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];
        GlobalRuntimeState::$throwErrorOnAccessViolation = true;

        $this->expectException(RuntimeException::class);

        $value = $this->makeAntlersTextValue('{{ title | lower }}');
        $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'HELLO',
        ], true, true);
    }

    #[Test]
    public function allow_list_does_not_affect_modifier_usage_in_trusted_templates()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = [];

        $result = $this->renderString('{{ title | lower }}', [
            'title' => 'HELLO',
        ], true, true);

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function allowed_tag_pattern_passes_user_content_guard()
    {
        GlobalRuntimeState::$isEvaluatingUserData = true;
        GlobalRuntimeState::$allowedContentTagPaths = ['collection:*'];

        $this->assertTrue($this->makeNodeProcessor()->guardRuntimeTag('collection:blog'));
    }

    #[Test]
    public function disallowed_tag_pattern_fails_user_content_guard()
    {
        GlobalRuntimeState::$isEvaluatingUserData = true;
        GlobalRuntimeState::$allowedContentTagPaths = ['collection:*'];

        $this->assertFalse($this->makeNodeProcessor()->guardRuntimeTag('form:create'));
    }

    #[Test]
    public function empty_tag_allow_list_blocks_all_tags_in_user_content_guard()
    {
        GlobalRuntimeState::$isEvaluatingUserData = true;
        GlobalRuntimeState::$allowedContentTagPaths = [];

        $this->assertFalse($this->makeNodeProcessor()->guardRuntimeTag('collection:blog'));
    }

    #[Test]
    public function tag_block_list_overrides_tag_allow_list_in_user_content_guard()
    {
        GlobalRuntimeState::$isEvaluatingUserData = true;
        GlobalRuntimeState::$allowedContentTagPaths = ['collection:*'];
        GlobalRuntimeState::$bannedContentTagPaths = ['collection:*'];

        $this->assertFalse($this->makeNodeProcessor()->guardRuntimeTag('collection:blog'));
    }

    #[Test]
    public function allow_list_does_not_affect_tag_usage_in_trusted_templates()
    {
        GlobalRuntimeState::$isEvaluatingUserData = false;
        GlobalRuntimeState::$allowedContentTagPaths = [];
        GlobalRuntimeState::$bannedTagPaths = [];

        $this->assertTrue($this->makeNodeProcessor()->guardRuntimeTag('form:create'));
    }

    private function makeAntlersTextValue(string $template): Value
    {
        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($field);

        return new Value($template, 'text_field', $textFieldtype);
    }

    private function makeNodeProcessor(): NodeProcessor
    {
        return new NodeProcessor(new Loader(), new EnvironmentDetails());
    }
}
