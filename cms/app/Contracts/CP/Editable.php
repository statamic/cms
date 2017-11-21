<?php

namespace Statamic\Contracts\CP;

interface Editable
{
    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl();
}
