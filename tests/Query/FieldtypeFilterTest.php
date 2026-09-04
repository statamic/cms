<?php

namespace Tests\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Entries as EntriesFieldtype;
use Statamic\Fieldtypes\Integer as IntegerFieldtype;
use Statamic\Fieldtypes\Terms as TermsFieldtype;
use Statamic\Fieldtypes\Text;
use Statamic\Query\Scopes\Filters\Fields\Dimensions;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FieldtypeFilterTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('completenessProvider')]
    public function it_determines_if_a_filter_is_complete($values, $expected)
    {
        $filter = (new Text)->setField(new Field('test', ['type' => 'text']))->filter();

        $this->assertEquals($expected, $filter->isComplete($values));
    }

    public static function completenessProvider()
    {
        return [
            'no operator' => [['value' => 'foo'], false],
            'operator but no value' => [['operator' => '='], false],
            'operator and value' => [['operator' => '=', 'value' => 'foo'], true],
            'zero string value' => [['operator' => '=', 'value' => '0'], true],
            'zero integer value' => [['operator' => '=', 'value' => 0], true],
            'null value' => [['operator' => '=', 'value' => null], false],
            'empty string value' => [['operator' => '=', 'value' => ''], false],
            'null operator without value' => [['operator' => 'null'], true],
            'not-null operator without value' => [['operator' => 'not-null'], true],
        ];
    }

    #[Test]
    #[DataProvider('dimensionsCompletenessProvider')]
    public function it_determines_if_a_dimensions_filter_is_complete($values, $expected)
    {
        $fieldtype = (new IntegerFieldtype)->setField(new Field('test', ['type' => 'integer']));
        $filter = new Dimensions($fieldtype);

        $this->assertEquals($expected, $filter->isComplete($values));
    }

    public static function dimensionsCompletenessProvider()
    {
        return [
            'all fields present' => [['dimension' => 'width', 'operator' => '=', 'value' => '100'], true],
            'zero value' => [['dimension' => 'width', 'operator' => '=', 'value' => 0], true],
            'zero string value' => [['dimension' => 'width', 'operator' => '=', 'value' => '0'], true],
            'null value' => [['dimension' => 'width', 'operator' => '=', 'value' => null], false],
            'missing value' => [['dimension' => 'width', 'operator' => '='], false],
            'missing dimension' => [['operator' => '=', 'value' => '100'], false],
        ];
    }

    #[Test]
    public function it_shows_the_terms_filter_badge_in_the_selected_site()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();

        Term::make('one')->taxonomy('tags')
            ->dataForLocale('en', ['title' => 'One'])
            ->dataForLocale('fr', ['title' => 'Un'])
            ->save();

        $filter = (new TermsFieldtype)
            ->setField(new Field('tags', ['type' => 'terms', 'taxonomies' => 'tags']))
            ->filter();

        Site::setSelected('fr');
        $this->assertEquals('Tags: Un', $filter->badge(['operator' => 'like', 'term' => 'one']));

        Site::setSelected('en');
        $this->assertEquals('Tags: One', $filter->badge(['operator' => 'like', 'term' => 'one']));
    }

    #[Test]
    public function it_applies_the_terms_filter_to_a_taxonomy_branch()
    {
        Taxonomy::make('categories')->structureContents([])->save();

        foreach (['animals', 'cat', 'furniture'] as $slug) {
            Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)])->save();
        }

        Taxonomy::find('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        Collection::make('blog')->taxonomies(['categories'])->save();

        (new EntryFactory)->collection('blog')->id('1')->slug('one')->data(['categories' => ['animals']])->create();
        (new EntryFactory)->collection('blog')->id('2')->slug('two')->data(['categories' => ['cat']])->create();
        (new EntryFactory)->collection('blog')->id('3')->slug('three')->data(['categories' => ['furniture']])->create();
        (new EntryFactory)->collection('blog')->id('4')->slug('four')->create();

        $filter = (new TermsFieldtype)
            ->setField(new Field('categories', ['type' => 'terms', 'taxonomies' => 'categories']))
            ->filter();

        $query = Entry::query()->where('collection', 'blog');
        $filter->apply($query, 'categories', ['operator' => 'like', 'term' => 'animals']);

        $this->assertEquals(['1', '2'], $query->get()->map->id()->sort()->values()->all());
    }

    #[Test]
    #[DataProvider('entriesFilterProvider')]
    public function it_applies_the_entries_filter($maxItems, $values, $expected)
    {
        Collection::make('pages')->save();
        Collection::make('topics')->save();

        (new EntryFactory)->collection('topics')->id('topic-1')->slug('topic-one')->data(['title' => 'Topic One'])->create();
        (new EntryFactory)->collection('topics')->id('topic-2')->slug('topic-two')->data(['title' => 'Topic Two'])->create();

        if ($maxItems === 1) {
            (new EntryFactory)->collection('pages')->id('page-a')->slug('page-a')->data(['related' => 'topic-1'])->create();
            (new EntryFactory)->collection('pages')->id('page-b')->slug('page-b')->data(['related' => 'topic-2'])->create();
        } else {
            (new EntryFactory)->collection('pages')->id('page-a')->slug('page-a')->data(['related' => ['topic-1']])->create();
            (new EntryFactory)->collection('pages')->id('page-b')->slug('page-b')->data(['related' => ['topic-1', 'topic-2']])->create();
        }

        (new EntryFactory)->collection('pages')->id('page-c')->slug('page-c')->create();

        $filter = (new EntriesFieldtype)
            ->setField(new Field('related', ['type' => 'entries', 'max_items' => $maxItems]))
            ->filter();

        $query = Entry::query()->where('collection', 'pages');
        $filter->apply($query, 'related', $values);

        $this->assertEquals($expected, $query->get()->map->id()->sort()->values()->all());
    }

    public static function entriesFilterProvider()
    {
        return [
            'single: is' => [1, ['operator' => '=', 'value' => 'topic-1'], ['page-a']],
            'single: isnt' => [1, ['operator' => '!=', 'value' => 'topic-1'], ['page-b', 'page-c']],
            'single: empty' => [1, ['operator' => 'null', 'value' => null], ['page-c']],
            'single: not empty' => [1, ['operator' => 'not-null', 'value' => null], ['page-a', 'page-b']],
            'single: no entry selected' => [1, ['operator' => '=', 'value' => null], ['page-a', 'page-b', 'page-c']],
            'multiple: is' => [null, ['operator' => '=', 'value' => 'topic-1'], ['page-a', 'page-b']],
            'multiple: is, only one match' => [null, ['operator' => '=', 'value' => 'topic-2'], ['page-b']],
            'multiple: isnt' => [null, ['operator' => '!=', 'value' => 'topic-1'], ['page-c']],
            'multiple: empty' => [null, ['operator' => 'null', 'value' => null], ['page-c']],
            'multiple: not empty' => [null, ['operator' => 'not-null', 'value' => null], ['page-a', 'page-b']],
        ];
    }

    #[Test]
    public function it_shows_the_entries_filter_badge()
    {
        Collection::make('topics')->save();

        (new EntryFactory)->collection('topics')->id('topic-1')->slug('topic-one')->data(['title' => 'Topic One'])->create();

        $filter = (new EntriesFieldtype)
            ->setField(new Field('related', ['type' => 'entries', 'display' => 'Related']))
            ->filter();

        $this->assertEquals('Related is Topic One', $filter->badge(['operator' => '=', 'value' => 'topic-1']));
        $this->assertEquals("Related isn't Topic One", $filter->badge(['operator' => '!=', 'value' => 'topic-1']));
        $this->assertEquals('Related empty', $filter->badge(['operator' => 'null', 'value' => null]));
        $this->assertEquals('Related not empty', $filter->badge(['operator' => 'not-null', 'value' => null]));
    }
}
