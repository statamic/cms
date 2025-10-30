<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Inertia\Inertia;
use Statamic\Facades\Git;
use Statamic\Http\Controllers\CP\CpController;

class GitController extends CpController
{
    public function index()
    {
        $statuses = Git::statuses();

        return Inertia::render('utilities/Git', [
            'statuses' => $statuses ? collect($statuses)->map(function ($status, $path) {
                return [
                    'path' => $path,
                    'totalCount' => $status->totalCount,
                    'addedCount' => $status->addedCount,
                    'modifiedCount' => $status->modifiedCount,
                    'deletedCount' => $status->deletedCount,
                    'status' => $status->status,
                ];
            })->values()->all() : null,
            'commitUrl' => cp_route('utilities.git.commit'),
        ]);
    }

    public function commit()
    {
        Git::commit();

        return back()->withSuccess(__('Content committed'));
    }
}
