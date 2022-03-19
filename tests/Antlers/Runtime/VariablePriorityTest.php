<?php

namespace Tests\Antlers\Runtime;

use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Exceptions\TaxonomyNotFoundException;
use Statamic\Modifiers\ModifierNotFoundException;
use Tests\Antlers\ParserTestCase;

class VariablePriorityTest extends ParserTestCase
{
    protected $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'collection' => [
                'articles' => [
                    ['title' => 'Test One'],
                    ['title' => 'Test Two'],
                ],
                'handle' => 'The Collection',
            ],
            'form' => [
                'handle' => 'The Form',
            ],
            'link' => 'Hello, there!',
            'taxonomy' => 'The Taxonomy',
        ];
    }

    private function render($template)
    {
        return $this->renderString($template, $this->data, true);
    }

    public function test_arrays_take_priority_over_tags()
    {
        $template = <<<'EOT'
{{ collection:articles }}<{{ title }}>{{ /collection:articles }}
EOT;

        $this->assertSame('<Test One><Test Two>', $this->render($template));
    }

    public function test_collection_tag_is_still_invoked()
    {
        $template = <<<'EOT'
{{ collection:news }}<{{ title }}>{{ /collection:news }}
EOT;
        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [news] not found');
        $this->render($template);
    }

    public function test_strings_can_be_used_inside_tags_with_similar_names()
    {
        $template = <<<'EOT'
{{ collection from="{collection}" }}{{ /collection }}
EOT;

        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [news] not found');
        $this->renderString($template, ['collection' => 'news'], true);
    }

    public function test_strings_can_override_tags_non_pair()
    {
        $this->assertSame('Hello, there!', $this->render('{{ link }}'));
    }

    public function test_strings_override_tags_even_with_tag_parameters()
    {
        $this->expectException(ModifierNotFoundException::class);
        $this->expectExceptionMessage('Modifier [to] not found');
        $this->render('{{ link to="the moon" }}');
    }

    public function test_taxonomy_can_be_used_as_variable_named()
    {
        $this->assertSame('The Taxonomy', $this->render('{{ taxonomy }}'));
    }

    public function test_taxonomy_tag_is_called()
    {
        $this->expectException(TaxonomyNotFoundException::class);
        $this->expectExceptionMessage('Taxonomy [tags] not found');
        $this->render('{{ taxonomy:tags }}{{ /taxonomy:tags }}');
    }

    public function test_common_variable_names_with_handles()
    {
        $this->assertSame('The Form', $this->render('{{ form:handle }}'));
        $this->assertSame('The Collection', $this->render('{{ collection:handle }}'));
    }

    public function test_form_tag_is_called()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Form with handle [iamaform] cannot be found.');
        $this->render('{{ form:create in="iamaform"}}');
    }

    public function test_similar_variable_names_are_prioritized_within_partials()
    {
        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [notacollection] not found');
        $this->renderString('{{ partial:collection collection="{collection}" }}', ['collection' => 'notacollection'], true);
    }
}
