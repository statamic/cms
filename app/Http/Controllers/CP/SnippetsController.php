<?php

namespace Statamic\Http\Controllers\CP;

/**
 * Controller for the snippets area
 */
class SnippetsController extends CpController
{
    /**
     * View for /cp/plugins
     */
    public function index()
    {
        $data = [
            'title' => 'Snippets'
        ];

        return view('statamic::snippets', $data);
    }
}
