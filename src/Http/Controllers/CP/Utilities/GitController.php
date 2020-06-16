<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Facades\Statamic\Git\Content;
use Statamic\Http\Controllers\CP\CpController;

class GitController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.git', [
            'statuses' => Content::statuses(),
        ]);
    }

    public function commit()
    {
        Content::commit();

        return back()->withSuccess(__('Content committed.'));
    }
}
