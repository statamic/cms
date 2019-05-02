<?php

namespace Tests;

use Statamic\API\Entry;
use Statamic\Stache\Stache;

class UrlBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /**
     * @var \Statamic\Contracts\Data\Content\UrlBuilder
     */
    protected $builder;

    /**
     * @var \Statamic\Data\Entries\File\Entry
     */
    protected $entry;

    public function setUp(): void
    {
        parent::setUp();

        $this->entry = \Statamic\API\Entry::make()
            ->id('post')
            ->collection(
                \Statamic\API\Collection::create('example')->dated(true)
            );

        $this->entry->in('en', function ($loc) {
            $loc
                ->slug('post')
                ->order('2015-01-02')
                ->data(['foo' => 'bar', 'slashed' => 'foo/bar']);
        });

        $this->entry->addLocalization(
            $this->entry->inOrClone('fr')->slug('le-post')
        );

        $this->builder = app('Statamic\Contracts\Data\Content\UrlBuilder')->content($this->entry);
    }

    public function testBuildsSimpleUrl()
    {
        $this->assertEquals('/blog/post', $this->builder->build('/blog/{slug}'));
    }

    public function testBuildsDateUrl()
    {
        $this->markTestSkipped(); // TODO: Come back when entries can return dates again.
        $this->assertEquals('/blog/2015/01/02/post', $this->builder->build('/blog/{year}/{month}/{day}/{slug}'));
    }

    public function testBuildsSimpleLocalizedUrl()
    {
        $this->builder->content($this->entry->in('fr'));
        $this->assertEquals('/blog/le-post', $this->builder->build('/blog/{slug}'));
    }

    public function testKeepsSlashesInValues()
    {
        $this->builder->content($this->entry);
        $this->assertEquals('/blog/foo/bar', $this->builder->build('/blog/{slashed}'));
    }

    function testVariablesCanBeMergedIn()
    {
        $this->assertEquals(
            '/bar/post',
            $this->builder->build('/{foo}/{slug}')
        );

        $this->assertEquals(
            '/baz/post',
            $this->builder->merge(['foo' => 'baz'])->build('/{foo}/{slug}')
        );
    }
}
