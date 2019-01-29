<?php

namespace Statamic\CP\Navigation;

use Statamic\API\Nav;

class DefaultNav
{
    /**
     * Make default nav items.
     */
    public static function make()
    {
        (new static)
            ->makeContentSection()
            ->makeToolsSection()
            ->makeUsersSection()
            ->makeSiteSection();
    }

    /**
     * Make content section items.
     *
     * @return $this
     */
    protected function makeContentSection()
    {
        Nav::content('Collections')->route('collections.index')->icon('content-writing');
        Nav::content('Structure')->route('structures.index')->icon('hierarchy-files');
        Nav::content('Taxonomies')->route('')->icon('tags');
        Nav::content('Assets')->route('assets.index')->icon('assets');
        Nav::content('Globals')->route('globals.index')->icon('earth');

        return $this;
    }

    /**
     * Make tools section items.
     *
     * @return $this
     */
    protected function makeToolsSection()
    {
        // Nav::tools('Forms')->route('forms.index')->icon('drawer-file');
        // Nav::tools('Updates')->route('')->icon('loading-bar')->view('nav.updates');

        // Nav::tools('Utilities')
        //     ->route('utilities.phpinfo')
        //     ->icon('settings-slider')
        //     ->children([

        //     ]);

        return $this;
    }

    /**
     * Make users section items.
     *
     * @return $this
     */
    protected function makeUsersSection()
    {
        // Nav::users('')->route('')->icon('');
        // Nav::users('')->route('')->icon('');
        // Nav::users('')->route('')->icon('');

        return $this;
    }

    /**
     * Make site section items.
     *
     * @return $this
     */
    protected function makeSiteSection()
    {
        // Nav::site('')->route('')->icon('');
        // Nav::site('')->route('')->icon('');
        // Nav::site('')->route('')->icon('');
        // Nav::site('')->route('')->icon('');

        return $this;
    }
}
