<?php

namespace Tests\Actions;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\DuplicateTerm;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DuplicateTermTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
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

    #[Test]
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

    #[Test]
    public function user_with_create_permission_is_authorized()
    {
        $this->setTestRoles([
            'access' => ['create tags terms'],
            'noaccess' => [],
        ]);

        Taxonomy::make('tags')->save();
        $userWithPermission = tap(User::make()->assignRole('access'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('noaccess'))->save();
        $items = collect([
            tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'Alfa']))->save(),
            tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo']))->save(),
        ]);

        $this->assertTrue((new DuplicateTerm)->authorize($userWithPermission, $items->first()));
        $this->assertTrue((new DuplicateTerm)->authorizeBulk($userWithPermission, $items));
        $this->assertFalse((new DuplicateTerm)->authorize($userWithoutPermission, $items->first()));
        $this->assertFalse((new DuplicateTerm)->authorizeBulk($userWithoutPermission, $items));
    }

    private function termData()
    {
        return Term::all()
            ->mapWithKeys(fn ($term) => [$term->slug() => $term->data()->all()])
            ->all();
    }
}
