<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Taxonomies\LocalizedTerm;

class LocalizedTermPolicyTest extends PolicyTestCase
{
    #[Test]
    public function term_is_viewable_with_view_permissions()
    {
        $user = $this->userWithPermissions(['view tags terms']);

        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        $termA = tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data([]))->save();
        $termB = tap(Term::make()->taxonomy('categories')->inDefaultLocale()->slug('alfa')->data([]))->save();

        $this->assertTrue($user->can('view', $termA));
        $this->assertFalse($user->can('edit', $termA));
        $this->assertFalse($user->can('view', $termB));
        $this->assertFalse($user->can('edit', $termB));
    }

    #[Test]
    public function term_is_viewable_and_editable_with_edit_permissions()
    {
        $user = $this->userWithPermissions(['edit tags terms']);

        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        $termA = tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data([]))->save();
        $termB = tap(Term::make()->taxonomy('categories')->inDefaultLocale()->slug('alfa')->data([]))->save();

        $this->assertTrue($user->can('view', $termA));
        $this->assertTrue($user->can('edit', $termA));
        $this->assertFalse($user->can('view', $termB));
        $this->assertFalse($user->can('edit', $termB));
    }

    #[Test]
    public function term_is_editable_with_edit_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de', 'es']);

        $user = $this->userWithPermissions([
            'edit tags terms',
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr', 'de'])->save();
        $term = tap(Term::make()->taxonomy('tags')->slug('alfa')
            ->dataForLocale('en', ['title' => 'alfa'])
            ->dataForLocale('fr', ['title' => 'le alfa'])
            ->dataForLocale('de', ['title' => 'das alfa'])
        )->save();

        $this->assertTrue($user->can('view', $term->in('en')));
        $this->assertTrue($user->can('edit', $term->in('en')));
        $this->assertFalse($user->can('view', $term->in('fr')));
        $this->assertFalse($user->can('edit', $term->in('fr')));
        $this->assertTrue($user->can('view', $term->in('de')));
        $this->assertTrue($user->can('edit', $term->in('de')));
    }

    #[Test]
    public function term_is_creatable_with_create_permissions()
    {
        $user = $this->userWithPermissions(['create alfa terms']);

        $taxonomyA = tap(Taxonomy::make('alfa'))->save();
        $taxonomyB = tap(Taxonomy::make('bravo'))->save();

        $this->assertTrue($user->can('create', [LocalizedTerm::class, $taxonomyA]));
        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomyB]));
        $this->assertTrue($user->can('store', [LocalizedTerm::class, $taxonomyA]));
        $this->assertFalse($user->can('store', [LocalizedTerm::class, $taxonomyB]));
    }

    #[Test]
    public function term_is_creatable_with_create_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de', 'es']);

        $user = $this->userWithPermissions([
            'create alfa terms',
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
            'access es site',
        ]);

        $taxonomy = tap(Taxonomy::make('alfa')->sites(['en', 'fr', 'de']))->save();

        $this->assertTrue($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('en')]));
        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('fr')]));
        $this->assertTrue($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('de')]));
        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('es')]));

        // If no site is specified, it should avoid checking site access.
        $this->assertTrue($user->can('create', [LocalizedTerm::class, $taxonomy]));
    }

    #[Test]
    public function term_is_not_creatable_without_create_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de', 'es']);

        $user = $this->userWithPermissions([
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
            'access es site',
        ]);

        $taxonomy = tap(Taxonomy::make('alfa')->sites(['en', 'fr', 'es']))->save();

        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('en')]));
        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('fr')]));
        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('de')]));
        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomy, Site::get('es')]));

        // If no site is specified, it should avoid checking site access.
        $this->assertFalse($user->can('create', [LocalizedTerm::class, $taxonomy]));
    }
}
