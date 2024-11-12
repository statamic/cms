<?php

namespace Tests\Forms;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubmissionQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_submissions()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['a' => true])->save();
        FormSubmission::make()->form($form)->data(['b' => true])->save();
        FormSubmission::make()->form($form)->data(['c' => true])->save();

        $submissions = FormSubmission::whereForm('test');
        $this->assertInstanceOf(Collection::class, $submissions);
        $this->assertEveryItemIsInstanceOf(Submission::class, $submissions);
    }

    #[Test]
    public function it_filters_using_wheres()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 'bar'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 'foo'])->save();

        $submissions = FormSubmission::query()->where('test', 'foo')->get();
        $this->assertEquals(['a', 'c'], $submissions->map->get('id')->sort()->values()->all());
    }

    #[Test]
    public function it_filters_using_or_wheres()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 'bar'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 'baz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'test' => 'raz'])->save();

        $submissions = FormSubmission::query()->where('test', 'foo')->orWhere('test', 'bar')->get();
        $this->assertEquals(['a', 'd', 'b'], $submissions->map->get('id')->values()->all());
    }

    #[Test]
    public function it_filters_using_or_where_ins()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 'bar'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 'baz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'test' => 'raz'])->save();

        $submissions = FormSubmission::query()->whereIn('test', ['foo', 'bar'])->orWhereIn('test', ['foo', 'raz'])->get();

        $this->assertEquals(['a', 'b', 'd', 'e'], $submissions->map->get('id')->values()->all());
    }

    #[Test]
    public function it_filters_using_or_where_not_ins()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 'bar'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 'baz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'test' => 'raz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'f', 'test' => 'taz'])->save();

        $submissions = FormSubmission::query()->whereNotIn('test', ['foo', 'bar'])->orWhereNotIn('test', ['foo', 'raz'])->get();

        $this->assertEquals(['c', 'f'], $submissions->map->get('id')->values()->all());
    }

    #[Test]
    public function it_filters_using_nested_wheres()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 'bar'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 'baz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'test' => 'raz'])->save();

        $submissions = FormSubmission::query()
            ->where(function ($query) {
                $query->where('test', 'foo');
            })
            ->orWhere(function ($query) {
                $query->where('test', 'baz');
            })
            ->orWhere('test', 'raz')
            ->get();

        $this->assertCount(4, $submissions);
        $this->assertEquals(['a', 'c', 'd', 'e'], $submissions->map->get('id')->sort()->values()->all());
    }

    #[Test]
    public function it_filters_using_nested_where_ins()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 'bar'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 'baz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'test' => 'raz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'f', 'test' => 'chaz'])->save();

        $submissions = FormSubmission::query()
            ->where(function ($query) {
                $query->where('test', 'foo');
            })
            ->orWhere(function ($query) {
                $query->whereIn('test', ['baz', 'raz']);
            })
            ->orWhere('test', 'chaz')
            ->get();

        $this->assertCount(5, $submissions);
        $this->assertEquals(['a', 'c', 'd', 'e', 'f'], $submissions->map->get('id')->sort()->values()->all());
    }

    #[Test]
    public function it_filters_using_nested_where_not_ins()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 'bar'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 'baz'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'test' => 'foo'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'test' => 'raz'])->save();

        $submissions = FormSubmission::query()
            ->where('test', 'foo')
            ->orWhere(function ($query) {
                $query->whereNotIn('test', ['baz', 'raz']);
            })
            ->get();

        $this->assertCount(3, $submissions);
        $this->assertEquals(['a', 'b', 'd'], $submissions->map->get('id')->sort()->values()->all());
    }

    #[Test]
    public function it_sorts()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'test' => 4])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'test' => 2])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'test' => 1])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'test' => 5])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'test' => 3])->save();

        $submissions = FormSubmission::query()->orderBy('test')->get();
        $this->assertEquals(['c', 'b', 'e', 'a', 'd'], $submissions->map->get('id')->all());
    }

    #[Test]
    public function submissions_are_found_using_where_column()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a', 'title' => 'Post 1', 'other_title' => 'Not Post 1'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b', 'title' => 'Post 2', 'other_title' => 'Not Post 2'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c', 'title' => 'Post 3', 'other_title' => 'Post 3'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'd', 'title' => 'Post 4', 'other_title' => 'Post 4'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'e', 'title' => 'Post 5', 'other_title' => 'Not Post 5'])->save();

        $submissions = FormSubmission::query()->whereColumn('title', 'other_title')->get();

        $this->assertCount(2, $submissions);
        $this->assertEquals(['c', 'd'], $submissions->map->get('id')->all());

        $submissions = FormSubmission::query()->whereColumn('title', '!=', 'other_title')->get();

        $this->assertCount(3, $submissions);
        $this->assertEquals(['a', 'b', 'e'], $submissions->map->get('id')->all());
    }

    #[Test]
    public function submissions_are_found_using_where_date()
    {
        $this->createWhereDateTestTerms();

        $entries = FormSubmission::query()->whereDate('date', '2021-11-15')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->sort()->values()->all());

        $entries = FormSubmission::query()->whereDate('date', 1637000264)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->sort()->values()->all());

        $entries = FormSubmission::query()->whereDate('date', '>=', '2021-11-15')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->sort()->values()->all());
    }

    #[Test]
    public function submissions_are_found_using_where_month()
    {
        $this->createWhereDateTestTerms();

        $entries = FormSubmission::query()->whereMonth('date', 11)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->sort()->values()->all());

        $entries = FormSubmission::query()->whereMonth('date', '<', 11)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->sort()->values()->all());
    }

    #[Test]
    public function submissions_are_found_using_where_day()
    {
        $this->createWhereDateTestTerms();

        $entries = FormSubmission::query()->whereDay('date', 15)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->sort()->values()->all());

        $entries = FormSubmission::query()->whereDay('date', '<', 15)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 2', 'Post 4'], $entries->map->title->sort()->values()->all());
    }

    #[Test]
    public function submissions_are_found_using_where_year()
    {
        $this->createWhereDateTestTerms();

        $entries = FormSubmission::query()->whereYear('date', 2021)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->sort()->values()->all());

        $entries = FormSubmission::query()->whereYear('date', '<', 2021)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->sort()->values()->all());
    }

    #[Test]
    public function submissions_are_found_using_where_time()
    {
        $this->createWhereDateTestTerms();

        $entries = FormSubmission::query()->whereTime('date', '09:00')->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->sort()->values()->all());

        $entries = FormSubmission::query()->whereTime('date', '>', '09:00')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 4'], $entries->map->title->sort()->values()->all());
    }

    private function createWhereDateTestTerms()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['title' => 'Post 1'])->id(1637008264)->save();
        FormSubmission::make()->form($form)->data(['title' => 'Post 2'])->id(1636621200)->save();
        FormSubmission::make()->form($form)->data(['title' => 'Post 3'])->id(1636934400)->save();
        FormSubmission::make()->form($form)->data(['title' => 'Post 4'])->id(1600008264)->save();
    }

    #[Test]
    public function submissions_are_found_using_where_json_contains()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => '1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        FormSubmission::make()->form($form)->data(['id' => '2', 'test_taxonomy' => ['taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        FormSubmission::make()->form($form)->data(['id' => '5', 'test_taxonomy' => ['taxonomy-5']])->save();

        $entries = FormSubmission::query()->whereJsonContains('test_taxonomy', ['taxonomy-1', 'taxonomy-3'])->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['3'], $entries->map->get('id')->all());

        $entries = FormSubmission::query()->whereJsonContains('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['1', '3'], $entries->map->get('id')->all());
    }

    #[Test]
    public function submissions_are_found_using_where_json_doesnt_contain()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => '1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        FormSubmission::make()->form($form)->data(['id' => '2', 'test_taxonomy' => ['taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        FormSubmission::make()->form($form)->data(['id' => '5', 'test_taxonomy' => ['taxonomy-5']])->save();

        $entries = FormSubmission::query()->whereJsonDoesntContain('test_taxonomy', ['taxonomy-1'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['2', '4', '5'], $entries->map->get('id')->all());

        $entries = FormSubmission::query()->whereJsonDoesntContain('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['2', '4', '5'], $entries->map->get('id')->all());
    }

    #[Test]
    public function submissions_are_found_using_or_where_json_contains()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => '1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        FormSubmission::make()->form($form)->data(['id' => '2', 'test_taxonomy' => ['taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        FormSubmission::make()->form($form)->data(['id' => '5', 'test_taxonomy' => ['taxonomy-5']])->save();

        $entries = FormSubmission::query()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonContains('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['1', '3', '5'], $entries->map->get('id')->all());
    }

    #[Test]
    public function submissions_are_found_using_or_where_json_doesnt_contain()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => '1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        FormSubmission::make()->form($form)->data(['id' => '2', 'test_taxonomy' => ['taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        FormSubmission::make()->form($form)->data(['id' => '5', 'test_taxonomy' => ['taxonomy-5']])->save();

        $entries = FormSubmission::query()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonDoesntContain('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['1', '3', '2', '4'], $entries->map->get('id')->all());
    }

    #[Test]
    public function submissions_are_found_using_where_json_length()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => '1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        FormSubmission::make()->form($form)->data(['id' => '2', 'test_taxonomy' => ['taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        FormSubmission::make()->form($form)->data(['id' => '4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        FormSubmission::make()->form($form)->data(['id' => '5', 'test_taxonomy' => ['taxonomy-5']])->save();

        $entries = FormSubmission::query()->whereJsonLength('test_taxonomy', 1)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['2', '5'], $entries->map->get('id')->all());
    }

    #[Test]
    public function submissions_are_found_using_offset()
    {
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['id' => 'a'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'b'])->save();
        FormSubmission::make()->form($form)->data(['id' => 'c'])->save();

        $submissions = FormSubmission::query()->get();
        $this->assertEquals(['a', 'b', 'c'], $submissions->map->get('id')->all());

        $submissions = FormSubmission::query()->offset(1)->get();
        $this->assertEquals(['b', 'c'], $submissions->map->get('id')->all());
    }
}
