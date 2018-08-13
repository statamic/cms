<?php

namespace Tests;

use Statamic\API\Entries;
use Statamic\API\Entry;
use Statamic\Stache\Stache;

class UrlBuilderTest extends TestCase
{
    /**
     * @var \Statamic\Contracts\Data\Content\UrlBuilder
     */
    protected $builder;

    /**
     * @var \Statamic\Data\Entries\File\Entry
     */
    protected $entry;

    public function setUp()
    {
        parent::setUp();

        // Add a blog collection to the stache
        $collection = Entries::createCollection('blog');
        $collection->data(['order' => 'date']);
        $this->app->make(Stache::class)
            ->store('collections')
            ->setPath('blog', 'collections/blog/folder.yaml')
            ->setItem('blog', $collection);

        $this->entry = Entry::create('post')
                      ->collection('blog')
                      ->order('2015-01-02')
                      ->with(['slashed' => 'foo/bar'])
                      ->get();

        $this->entry->in('fr')->set('slug', 'le-post');

        $this->builder = app('Statamic\Contracts\Data\Content\UrlBuilder')->content($this->entry);
    }

    public function testBuildsSimpleUrl()
    {
        $this->assertEquals('/blog/post', $this->builder->build('/blog/{slug}'));
    }

    public function testBuildsDateUrl()
    {
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
}
