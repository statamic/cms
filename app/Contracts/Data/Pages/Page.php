<?php

namespace Statamic\Contracts\Data\Pages;

use Statamic\Contracts\Data\Content\Content;

interface Page extends Content
{
    /**
     * Determine whether this page has entries
     *
     * @return bool|null
     */
    public function hasEntries();

    /**
     * Get the entries mounted to this page
     *
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public function entries();

    /**
     * Get the name of the entry collection mounted to this page
     *
     * @return string
     */
    public function entriesCollection();

    /**
     * Get this page's child pages
     *
     * @param null|int $depth
     * @return \Statamic\Data\Pages\PageCollection
     */
    public function children($depth = null);

    /**
     * Get the parent page
     *
     * @return Page|null
     */
    public function parent();
}
