<?php

namespace Tests\Support;

use Statamic\Facades\Compare;
use Statamic\Support\Str;
use Tests\TestCase;

class StrTest extends TestCase
{
    /** @test */
    public function undefined_methods_get_passed_to_stringy()
    {
        $this->assertFalse(method_exists(Str::class, 'last'));
        $this->assertEquals('bar', Str::last('foobar', 3));
    }

    /** @test */
    public function it_makes_sentence_lists()
    {
        $this->assertEquals('this', Str::makeSentenceList(['this']));
        $this->assertEquals('this and that', Str::makeSentenceList(['this', 'that']));
        $this->assertEquals('this, that, and the other', Str::makeSentenceList(['this', 'that', 'the other']));

        $this->assertEquals('this', Str::makeSentenceList(['this'], '&'));
        $this->assertEquals('this & that', Str::makeSentenceList(['this', 'that'], '&'));
        $this->assertEquals('this, that, & the other', Str::makeSentenceList(['this', 'that', 'the other'], '&'));

        $this->assertEquals('this', Str::makeSentenceList(['this'], 'and', false));
        $this->assertEquals('this and that', Str::makeSentenceList(['this', 'that'], 'and', false));
        $this->assertEquals('this, that and the other', Str::makeSentenceList(['this', 'that', 'the other'], 'and', false));
    }

    /** @test */
    public function it_strips_tags()
    {
        $html = '<h1>heading</h1> <b>bold</b>';
        $this->assertEquals('heading bold', Str::stripTags($html));
        $this->assertEquals('heading <b>bold</b>', Str::stripTags($html, ['h1']));
        $this->assertEquals('<h1>heading</h1> <b>bold</b>', Str::stripTags($html, ['em']));
        $this->assertEquals('heading <b>bold</b>', Str::stripTags($html, ['h1', 'em']));
        $this->assertEquals('heading bold', Str::stripTags($html, ['h1', 'b']));
    }

    /** @test */
    public function it_makes_slugs()
    {
        $this->assertEquals('foo-bar-baz', Str::slug('foo bar baz'));
        $this->assertEquals('foo-bar-baz', Str::slug('Foo Bar Baz'));
        $this->assertEquals('foo-bar-baz', Str::slug('foo-bar-baz'));
        $this->assertEquals('foo_bar-baz', Str::slug('foo_bar baz'));
    }

    /** @test */
    public function it_converts_studly_to_slug()
    {
        $this->assertEquals('foo-bar-baz', Str::studlyToSlug('FooBarBaz'));
    }

    /** @test */
    public function it_converts_studly_to_title()
    {
        $this->assertEquals('Foo Bar Baz', Str::studlyToTitle('FooBarBaz'));
    }

    /** @test */
    public function it_converts_slug_to_title()
    {
        $this->assertEquals('Foo Bar Baz', Str::studlyToTitle('foo-bar-baz'));
    }

    /** @test */
    public function it_checks_for_a_url()
    {
        $this->assertTrue(Str::isUrl('http://example.com'));
        $this->assertTrue(Str::isUrl('https://example.com'));
        $this->assertTrue(Str::isUrl('ftp://example.com'));
        $this->assertTrue(Str::isUrl('mailto:bob@down.com'));
        $this->assertTrue(Str::isUrl('/relative'));
        $this->assertFalse(Str::isUrl('test'));
    }

    /** @test */
    public function it_deslugifies_a_slug()
    {
        $this->assertEquals('foo bar baz', Str::deslugify('foo-bar-baz'));
    }

    /** @test */
    public function it_gets_file_size_for_humans()
    {
        $this->assertEquals('0 B', Str::fileSizeForHumans(0));
        $this->assertEquals('1.00 KB', Str::fileSizeForHumans(1024));
        $this->assertEquals('1.75 KB', Str::fileSizeForHumans(1792));
        $this->assertEquals('1.00 MB', Str::fileSizeForHumans(1048576));
        $this->assertEquals('1.75 MB', Str::fileSizeForHumans(1835008));
        $this->assertEquals('1.00 GB', Str::fileSizeForHumans(1073741824));
        $this->assertEquals('1.75 GB', Str::fileSizeForHumans(1879048192));

        $this->assertEquals('0 B', Str::fileSizeForHumans(0, 0));
        $this->assertEquals('1 KB', Str::fileSizeForHumans(1024, 0));
        $this->assertEquals('2 KB', Str::fileSizeForHumans(1792, 0));
        $this->assertEquals('1 MB', Str::fileSizeForHumans(1048576, 0));
        $this->assertEquals('2 MB', Str::fileSizeForHumans(1835008, 0));
        $this->assertEquals('1 GB', Str::fileSizeForHumans(1073741824, 0));
        $this->assertEquals('2 GB', Str::fileSizeForHumans(1879048192, 0));
    }

    /** @test */
    public function it_gets_time_for_humans()
    {
        $this->assertEquals('1ms', Str::timeForHumans(1));
        $this->assertEquals('1s', Str::timeForHumans(1000));
        $this->assertEquals('1.5s', Str::timeForHumans(1500));
        $this->assertEquals('1.57s', Str::timeForHumans(1570));
    }

    /** @test */
    public function it_widonts()
    {
        $this->assertEquals('one two&nbsp;three', Str::widont('one two three'));
        $this->assertEquals('<p>one two&nbsp;three</p>', Str::widont('<p>one two three</p>'));
    }

    /** @test */
    public function it_compares_two_strings()
    {
        Compare::shouldReceive('strings')->with('one', 'two')->once();
        Str::compare('one', 'two');
    }

    /** @test */
    public function it_modifies_strings_with_multiple_methods_at_once()
    {
        $this->assertEquals(
            'this, that, and the&nbsp;other',
            Str::modifyMultiple(['this', 'that', 'the other'], ['makeSentenceList', 'widont'])
        );
    }

    /** @test */
    public function it_makes_tailwind_width_classes()
    {
        $this->assertEquals('w-1/4', Str::tailwindWidthClass(25));
        $this->assertEquals('w-1/3', Str::tailwindWidthClass(33));
        $this->assertEquals('w-1/2', Str::tailwindWidthClass(50));
        $this->assertEquals('w-2/3', Str::tailwindWidthClass(66));
        $this->assertEquals('w-3/4', Str::tailwindWidthClass(75));
        $this->assertEquals('w-full', Str::tailwindWidthClass(100));
        $this->assertEquals('w-full', Str::tailwindWidthClass('foo'));
    }

    /** @test */
    public function it_converts_to_boolean_strings()
    {
        $this->assertEquals('true', Str::bool(true));
        $this->assertEquals('false', Str::bool(false));
    }

    /** @test */
    public function it_converts_to_booleans()
    {
        $this->assertTrue(Str::toBool('true'));
        $this->assertTrue(Str::toBool('yes'));
        $this->assertTrue(Str::toBool('really anything'));

        $this->assertFalse(Str::toBool('false'));
        $this->assertFalse(Str::toBool('no'));
        $this->assertFalse(Str::toBool('0'));
        $this->assertFalse(Str::toBool(''));
        $this->assertFalse(Str::toBool('-1'));
    }

    /**
     * @test
     * @see https://github.com/statamic/cms/pull/3698
     **/
    public function it_replaces_strings()
    {
        $this->assertEquals('FÒÔ bàř', Str::replace('fòô bàř', 'fòô', 'FÒÔ'));
    }
}
