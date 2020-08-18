<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Statamic\Mail\Test;

class EmailController
{
    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Mail::to($request->email)->send(new Test);

        return back()->withSuccess(__('Test email sent.'));
    }
}
