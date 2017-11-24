<?php

namespace Statamic\Contracts\Data\Globals;

use Statamic\Contracts\Data\Content\Content;

interface GlobalSet extends Content
{
    /**
     * Get or set the title
     *
     * @param string|null $title
     * @return mixed
     */
    public function title($title = null);
}
