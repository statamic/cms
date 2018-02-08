<?php

namespace Tests;

use Statamic\API\Page;
use Statamic\API\Term;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Taxonomy;
use Statamic\API\GlobalSet;
use Statamic\API\Collection;

class StacheTest extends TestCase
{
    public function setUp()
    {
        // todo: obviously temporary.
        // we want to use a fake stache in all tests, except this one.
        $GLOBALS['need_real_stache_for_testing_kthx'] = true;
        parent::setUp();
    }

    /** @test */
    function pages_are_created()
    {
        $page = Page::find('home');

        $this->assertEquals('Home Page', $page->get('title'));
        $this->assertEquals('Home Page in French', $page->in('fr')->get('title'));
    }

    /** @test */
    function collections_are_created()
    {
        $this->assertEquals(1, Collection::all()->count());
        $this->assertEquals('dated', Collection::all()->first()->path());
    }

    /** @test */
    function entries_are_created()
    {
        $entry = Entry::find('test-dated-entry');

        $this->assertEquals('Test Dated Entry', $entry->get('title'));
        $this->assertEquals('Test Dated Entry in French', $entry->in('fr')->get('title'));
    }

    /** @test */
    function globals_are_created()
    {
        $this->assertEquals(2, GlobalSet::all()->count());

        tap(GlobalSet::find('global'), function ($global) {
            $this->assertEquals('The Foo Global', $global->get('foo'));
            $this->assertEquals('The Foo Global in French', $global->in('fr')->get('foo'));
        });

        tap(GlobalSet::find('another-global'), function ($global) {
            $this->assertEquals('The another.bar global', $global->get('bar'));
            $this->assertEquals('The another.bar global in French', $global->in('fr')->get('bar'));
        });
    }

    /** @test */
    function taxonomies_are_created()
    {
        $this->assertEquals(1, Taxonomy::all()->count());
        $this->assertEquals('Test Tags', Taxonomy::whereHandle('tags')->title());
    }

    /** @test */
    function taxonomy_terms_are_created_from_within_content()
    {
        $this->assertEquals(2, Term::all()->count());

        $term = Term::find('tags/test-term-in-content');
        $this->assertEquals('test-term-in-content', $term->title());
        $this->assertEquals('test-term-in-content', $term->in('fr')->title());
        tap($term->collection(), function ($entries) {
            $this->assertEquals(1, $entries->count());
            $this->assertEquals('Test Dated Entry', $entries->first()->get('title'));
        });

        $term = Term::find('tags/test-file-based-term');
        $this->assertEquals('File based term with title customized in file', $term->title());
        $this->assertEquals('Customized French Title', $term->in('fr')->title());
        tap($term->collection(), function ($entries) {
            $this->assertEquals(1, $entries->count());
            $this->assertEquals('Test Dated Entry', $entries->first()->get('title'));
        });
    }

    /** @test */
    function users_are_created()
    {
        $this->assertEquals(1, User::all()->count());
        tap(User::all()->first(), function ($user) {
            $this->assertEquals('testerson', $user->username());
            $this->assertEquals('Test Testerson', $user->get('name'));
        });
    }
}