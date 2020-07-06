<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Statamic\Facades\Git;
use Statamic\Http\Controllers\CP\CpController;

class GitController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.git', [
            'statuses' => Git::statuses(),
        ]);
    }

    public function commit()
    {
        Git::commit();

        return back()->withSuccess(__('Content committed'));
    }
}
