<?php

namespace Tests\Antlers;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
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

        $parsed = (string) Antlers::parse($template, ['title' => 'Outside']);

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

        $this->assertEquals('baz', (string) Antlers::parse('{{ test variable="{bar }" }}', ['bar' => 'baz']));
        $this->assertEquals('baz', (string) Antlers::parse('{{ test variable="{ bar}" }}', ['bar' => 'baz']));
        $this->assertEquals('baz', (string) Antlers::parse('{{ test variable="{ bar }" }}', ['bar' => 'baz']));
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
}
