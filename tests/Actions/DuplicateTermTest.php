<?php

namespace Tests\Actions;

use Statamic\Actions\DuplicateTerm;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DuplicateTermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_duplicates_a_term()
    {
        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'Alfa'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('charlie')->data(['title' => 'Charlie'])->save();

        $this->assertEquals([
            'alfa' => ['title' => 'Alfa'],
            'bravo' => ['title' => 'Bravo'],
            'charlie' => ['title' => 'Charlie'],
        ], $this->termData());

        (new DuplicateTerm)->run(collect([
            Term::find('tags::alfa'),
            Term::find('tags::charlie'),
        ]), collect());

        $this->assertEquals([
            'alfa' => ['title' => 'Alfa'],
            'bravo' => ['title' => 'Bravo'],
            'charlie' => ['title' => 'Charlie'],
            'alfa-1' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'tags::alfa'],
            'charlie-1' => ['title' => 'Charlie (Duplicated)', 'duplicated_from' => 'tags::charlie'],
        ], $this->termData());
    }

    /** @test */
    public function it_increments_the_number_if_duplicate_already_exists()
    {
        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'Alfa'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa-1')->data(['title' => 'Alfa (Duplicated)'])->save();

        (new DuplicateTerm)->run(collect([
            Term::find('tags::alfa'),
        ]), collect());

        $this->assertEquals([
            'alfa' => ['title' => 'Alfa'],
            'alfa-1' => ['title' => 'Alfa (Duplicated)'],
            'alfa-2' => ['title' => 'Alfa (Duplicated) (2)', 'duplicated_from' => 'tags::alfa'],
        ], $this->termData());
    }

    private function termData()
    {
        return Term::all()
            ->mapWithKeys(fn ($term) => [$term->slug() => $term->data()->all()])
            ->all();
    }
}
