<?php

namespace Tests\Antlers\Sandbox;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class AugmentedAssignmentTest extends ParserTestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesViews;

    public function test_tag_results_containing_augmentable_items_are_augmented()
    {
        Taxonomy::make('tags')->save();

        Collection::make('blog')->routes(['en' => '{slug}'])->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->id('1')->slug('one')->data(['title' => 'One', 'tags' => ['rad', 'test', 'test-two']])->create();
        EntryFactory::collection('blog')->id('2')->slug('two')->data(['title' => 'Two', 'tags' => ['rad', 'two']])->create();
        EntryFactory::collection('blog')->id('3')->slug('three')->data(['title' => 'Three', 'tags' => ['meh']])->create();
        EntryFactory::collection('blog')->id('4')->slug('four')->create();

        $this->withFakeViews();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $template = <<<'EOT'
{{ posts = {collection:blog}; }}

{{ posts scope="post" }} {{ post:tags }} {{ title }} {{ /post:tags }}{{ /posts }}
EOT;
        $this->viewShouldReturnRaw('default', $template);

        $resp = $this->get('one')->assertOk();

        $this->assertSame('rad  test  test-two   meh   rad  two', trim($resp->getContent()));
    }

    public function test_tags_returning_collections_resolve_correctly()
    {
        (new class extends Tags
        {
            public static $handle = 'test';

            public function index()
            {
                return collect(['a', 'b', 'c']);
            }
        })::register();

        $template = <<<'EOT'
{{ results = {test}; }}

{{ results }}{{ value }}{{ /results }}
EOT;

        $this->assertSame('abc', trim($this->renderString($template, [], true)));
    }
}
