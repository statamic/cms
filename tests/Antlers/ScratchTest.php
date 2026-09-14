<?php

namespace Tests\Antlers;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ScratchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function tag_variables_should_not_leak_outside_its_tag_pair()
    {
        EntryFactory::collection('test')->id('one')->slug('one')->data(['title' => 'One'])->create();
        EntryFactory::collection('test')->id('two')->slug('two')->data(['title' => 'Two'])->create();

        // note: not specific to the collection tag
        $template = '{{ title }} {{ collection:test }}{{ title }} {{ /collection:test }} {{ title }}';
        $expected = 'Outside One Two  Outside';

        $parsed = (string) Antlers::parse($template, ['title' => 'Outside'], true);

        $this->assertEquals($expected, $parsed);
    }

    #[Test]
    public function if_with_extra_leading_spaces_should_work()
    {
        $parsed = (string) Antlers::parse('{{  if yup }}you bet{{ else }}nope{{ /if }}', ['yup' => true]);

        $this->assertEquals('you bet', $parsed);
    }

    #[Test]
    public function interpolated_parameter_with_extra_space_should_work()
    {
        $this->app['statamic.tags']['test'] = \Tests\Fixtures\Addon\Tags\TestTags::class;

        $this->assertEquals('baz', (string) Antlers::parse('{{ test variable="{bar }" }}', ['bar' => 'baz'], true));
        $this->assertEquals('baz', (string) Antlers::parse('{{ test variable="{ bar}" }}', ['bar' => 'baz'], true));
        $this->assertEquals('baz', (string) Antlers::parse('{{ test variable="{ bar }" }}', ['bar' => 'baz'], true));
    }

    public function test_runtime_can_parse_expanded_ascii_characters()
    {
        $template = <<<'EOT'
<h1>{{ title }}</h1><h1>{{ title replace="®|<sup>®®</sup>" }}</h1>
<{{ title }}>
{{ my_var = '¥¦§¨©ª«¬®¯°±²³´µ¶¼½¾¿À' }}
<h1>{{ title }}</h1><h1>{{ title replace="®|<sup>®®</sup>" }}</h1>
<{{ my_var }}>
    {{ another_var = 'aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz' }}after
next line
    <before>{{ another_var }}<after>
EOT;

        $data = [
            'title' => 'PRODUCT®',
        ];

        $expected = <<<'EOT'
<h1>PRODUCT®</h1><h1>PRODUCT<sup>®®</sup></h1>
<PRODUCT®>

<h1>PRODUCT®</h1><h1>PRODUCT<sup>®®</sup></h1>
<¥¦§¨©ª«¬®¯°±²³´µ¶¼½¾¿À>
    after
next line
    <before>aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz<after>
EOT;

        $this->assertSame($expected, (string) Antlers::parse($template, $data));
    }

    #[Test]
    public function reduce_for_antlers_restores_global_runtime_state_when_early_returning_with_model()
    {
        $prevUserData = GlobalRuntimeState::$isEvaluatingUserData;
        $prevData = GlobalRuntimeState::$isEvaluatingData;

        try {
            GlobalRuntimeState::$isEvaluatingUserData = false;
            GlobalRuntimeState::$isEvaluatingData = false;

            $model = new class extends Model
            {
                //
            };
            $parser = Antlers::parser();

            $result = PathDataManager::reduceForAntlers($model, $parser, [], true);

            $this->assertSame($model, $result);
            $this->assertFalse(GlobalRuntimeState::$isEvaluatingUserData, 'isEvaluatingUserData should be restored after Model early return');
            $this->assertFalse(GlobalRuntimeState::$isEvaluatingData, 'isEvaluatingData should be restored after Model early return');
        } finally {
            GlobalRuntimeState::$isEvaluatingUserData = $prevUserData;
            GlobalRuntimeState::$isEvaluatingData = $prevData;
        }
    }

    #[Test]
    public function check_for_field_value_restores_state_when_value_throws()
    {
        $prev = GlobalRuntimeState::$isEvaluatingUserData;

        try {
            GlobalRuntimeState::$isEvaluatingUserData = false;

            $throwingValue = new Value(function () {
                throw new \RuntimeException('Intentional field value failure');
            });

            $env = new Environment();
            $method = new \ReflectionMethod($env, 'checkForFieldValue');

            try {
                $method->invoke($env, $throwingValue);
            } catch (\Throwable $e) {
                $this->assertSame('Intentional field value failure', $e->getMessage());
            }

            $this->assertFalse(
                GlobalRuntimeState::$isEvaluatingUserData,
                'isEvaluatingUserData should be restored after exception in checkForFieldValue'
            );
        } finally {
            GlobalRuntimeState::$isEvaluatingUserData = $prev;
        }
    }

    #[Test]
    public function view_with_condition_on_throwing_field_value_restores_state_so_later_output_is_correct()
    {
        app('statamic.tags')['trigger_value_exception'] = TriggerValueExceptionTag::class;
        app('statamic.modifiers')['scratch_test_upper'] = ScratchTestUppercaseModifier::class;

        $template = '{{ trigger_value_exception }} {{ title | scratch_test_upper }}';
        $data = ['title' => 'hello'];

        $output = (string) Antlers::parse($template, $data, true);

        $this->assertSame('trusted HELLO', $output, 'Trusted state was not restored');
    }
}

class TriggerValueExceptionTag extends \Statamic\Tags\Tags
{
    public function index()
    {
        $throwingValue = new Value(function () {
            throw new \RuntimeException('Intentional field value failure');
        });

        $env = new Environment();
        $env->setData($this->context->all());
        $method = new \ReflectionMethod($env, 'checkForFieldValue');
        try {
            $method->invoke($env, $throwingValue);
        } catch (\Throwable $e) {
            //
        }

        $trusted = ! \Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState::$isEvaluatingUserData;

        return $trusted ? 'trusted' : 'untrusted';
    }
}

class ScratchTestUppercaseModifier extends \Statamic\Modifiers\Modifier
{
    public function index($value, $params, $context)
    {
        return is_string($value) ? strtoupper($value) : $value;
    }
}
