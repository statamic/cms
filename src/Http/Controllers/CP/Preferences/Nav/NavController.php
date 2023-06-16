<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Statamic;

class NavController extends Controller
{
    public function index()
    {
        if (! Statamic::pro() || User::current()->cannot('manage preferences')) {
            return redirect(cp_route('preferences.nav.user.edit'));
        }

        return view('statamic::nav.index');
    }
}
