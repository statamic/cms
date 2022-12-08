<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Statamic;

class NavController extends Controller
{
    public function index()
    {
        abort_unless(Statamic::pro() && User::current()->isSuper(), 403);

        return view('statamic::nav.index');
    }
}
