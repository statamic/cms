<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;

class NavController extends Controller
{
    public function index()
    {
        abort_unless(User::current()->isSuper(), 403);

        return view('statamic::nav.index');
    }
}
