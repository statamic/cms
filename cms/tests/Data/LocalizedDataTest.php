<?php

namespace Statamic\Tests\Data;

use Statamic\Data\Pages\Page;
use Statamic\Data\LocalizedData;

class LocalizedDataTest extends \PHPUnit_Framework_TestCase
{
    protected $page;
    protected $localized;

    public function setUp()
    {
        parent::setUp();

        $this->page = new SamplePage;

        $this->localized = new LocalizedData('fr', $this->page);
    }

    public function test_regular_data_gets_set_and_retrieved()
    {
        $this->page->set('hello', 'Hello');

        $this->assertEquals('Hello', $this->page->get('hello'));
    }

    public function test_localized_data_gets_set_and_get()
    {
        $this->page->set('hello', 'Hello');
        $this->localized->set('hello', 'Bonjour');

        $this->assertEquals('Bonjour', $this->localized->get('hello'));
    }

    public function test_localized_data_gets_url()
    {
        $this->page->slug('test');
        $this->localized->slug('le-test');

        $this->assertEquals('/test', $this->page->url());
        $this->assertEquals('/le-test', $this->localized->url());
    }
}

class SamplePage extends Page
{
    public function url($url = null)
    {
        return '/'.$this->slug();
    }

    public function slug($slug = null)
    {
        if (is_null($slug)) {
            if ($this->isDefaultLocale()) {
                return $this->attributes['slug'];
            } else {
                return $this->get('slug');
            }
        }

        if ($this->isDefaultLocale()) {
            $this->attributes['slug'] = $slug;
        } else {
            $this->set('slug', $slug);
        }
    }

    public function cascadingData()
    {
        return [];
    }
}