<?php

namespace Tests\Support;

use Statamic\Support\Html;
use Tests\TestCase;

class HtmlTest extends TestCase
{
    public function testDl()
    {
        $list = [
            'foo' => 'bar',
            'bing' => 'baz',
        ];

        $attributes = ['class' => 'example'];

        $result = Html::dl($list, $attributes);

        $this->assertEquals('<dl class="example"><dt>foo</dt><dd>bar</dd><dt>bing</dt><dd>baz</dd></dl>', $result);
    }

    public function testOl()
    {
        $list = ['foo', 'bar', '&amp;'];

        $attributes = ['class' => 'example'];

        $ol = Html::ol($list, $attributes);

        $this->assertEquals('<ol class="example"><li>foo</li><li>bar</li><li>&amp;</li></ol>', $ol);
    }

    /**
     * @test
     */
    public function it_returns_empty_string_when_no_list_items_given(): void
    {
        $list = [];
        $attributes = ['class' => 'example'];

        $ol = Html::ol($list, $attributes);
        $this->assertEquals('', $ol);

        $ul = Html::ul($list, $attributes);
        $this->assertEquals('', $ul);
    }

    public function testUl()
    {
        $list = ['foo', 'bar', '&amp;'];

        $attributes = ['class' => 'example'];

        $ul = Html::ul($list, $attributes);

        $this->assertEquals('<ul class="example"><li>foo</li><li>bar</li><li>&amp;</li></ul>', $ul);
    }

    public function testFavicon()
    {
        $result = Html::favicon('http://foo.com/bar.ico');

        $this->assertEquals('<link rel="shortcut icon" type="image/x-icon" href="http://foo.com/bar.ico">', $result);
    }

    public function testLink()
    {
        $result1 = Html::link('http://www.example.com', '<span>Example.com</span>', ['class' => 'example-link']);

        $result2 = Html::link('https://a.com/b?id=4&not_id=5', 'URL which needs escaping');

        $this->assertEquals('<a href="http://www.example.com" class="example-link">&lt;span&gt;Example.com&lt;/span&gt;</a>', $result1);
        $this->assertEquals('<a href="https://a.com/b?id=4&amp;not_id=5">URL which needs escaping</a>', $result2);
    }

    public function testMailto()
    {
        $html = $this->mock('Statamic\Support\Html[obfuscate,email]');
        $html->shouldReceive('obfuscate', 'email')->andReturnUsing(function () {
            $args = func_get_args();

            return $args[0];
        });

        $result1 = $html->mailto('person@example.com', '<span>First Name Last</span>', ['class' => 'example-link'], true);

        $result2 = $html->mailto('person@example.com', '<span>First Name Last</span>', ['class' => 'example-link'], false);

        $this->assertEquals('<a href="mailto:person@example.com" class="example-link">&lt;span&gt;First Name Last&lt;/span&gt;</a>', $result1);
        $this->assertEquals('<a href="mailto:person@example.com" class="example-link"><span>First Name Last</span></a>', $result2);
    }
}
