<?php

namespace Tests\Data;

use Tests\TestCase;
use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Config;
use Statamic\API\Entries;
use Statamic\Stache\Stache;

class EntryTest extends TestCase
{
    /** @var  \Statamic\Data\Entries\Entry */
    protected $entry;

    public function setUp()
    {
        parent::setUp();

        // Add a blog collection to the stache
        $this->collection = Entries::createCollection('blog');
        $this->collection->data(['order' => 'date', 'template' => 'blog/post']);
        $this->stache = $this->app->make(Stache::class);
        $this->stache->store('collections')
            ->setItem('blog', $this->collection)
            ->setPath('blog', 'collections/blog.yaml');

        $this->entry = Entry::create('post')->collection('blog')->with([
            'title' => 'Test',
            'foo' => 'bar'
        ])->get();
    }

    public function testGetsSlug()
    {
        $this->assertEquals('post', $this->entry->slug());
    }

    public function testGetsCollection()
    {
        $this->assertEquals('blog', $this->entry->collectionName());
    }

    public function testDoesntGetUrl()
    {
        $this->expectException('Statamic\Exceptions\InvalidEntryTypeException');

        $this->collection->route('/blog/{year}/{slug}');
        $this->stache->store('collections')->setItem('blog', $this->collection);

        $this->entry->url();
    }

    public function testGetsUrl()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'url' => 'http://talons-beard.dev/']
            ]
        ]);

        $this->collection->route('/blog/{slug}');
        $this->stache->store('collections')->setItem('blog', $this->collection);

        $this->entry->order('2015-02-01');

        $this->assertEquals('/blog/post', $this->entry->url());

        $this->collection->route('/blog/{year}/{month}/{day}/{slug}');
        $this->stache->store('collections')->setItem('blog', $this->collection);

        $this->assertEquals('/blog/2015/02/01/post', $this->entry->url());
        $this->assertEquals('http://talons-beard.dev/blog/2015/02/01/post', $this->entry->absoluteUrl());
    }

    public function testGetsPath()
    {
        $this->assertEquals('collections/blog/post.md', $this->entry->path());

        $this->entry->unpublish();

        $this->assertEquals('collections/blog/_post.md', $this->entry->path());

        $this->entry->order('2015-01-01');

        $this->entry->publish();

        $this->assertEquals('collections/blog/2015-01-01.post.md', $this->entry->path());

        $this->entry->unpublish();

        $this->assertEquals('collections/blog/_2015-01-01.post.md', $this->entry->path());

        $this->entry->publish();
        $this->entry->order(1);

        $this->assertEquals('collections/blog/1.post.md', $this->entry->path());

        $this->entry->unpublish();

        $this->assertEquals('collections/blog/_1.post.md', $this->entry->path());
    }

    public function testGetsTemplate()
    {
        $this->assertEquals(['blog/post', 'post', 'default'], $this->entry->template());

        $this->entry->set('template', 'my-template');

        $this->assertEquals(['my-template', 'post', 'default'], $this->entry->template());
    }

    public function testGetsDate()
    {
        $this->entry->order('2015-01-02');
        $this->assertInstanceOf('Carbon\Carbon', $this->entry->date());
    }

    public function testThrowExceptionWhenGettingDateForNonDateEntry()
    {
        $this->expectException('Statamic\Exceptions\InvalidEntryTypeException');

        $this->entry->date();
    }

    public function testGetsOrderType()
    {
        // this will be whatever is defined in collections/blog/folder.yaml
        $this->assertEquals('date', $this->entry->orderType());
    }

    public function testGetsLayout()
    {
        $this->assertEquals(config('statamic.theming.views.layout'), $this->entry->layout());

        $this->entry->set('layout', 'my-layout');

        $this->assertEquals('my-layout', $this->entry->layout());
    }
}
