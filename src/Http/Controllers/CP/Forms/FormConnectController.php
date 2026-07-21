<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Inertia\Inertia;
use Statamic\Facades\FormConnection;
use Statamic\Forms\Connections\Connection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\ProvidesFormAbilities;

class FormConnectController extends CpController
{
    use ProvidesFormAbilities;

    public function index($form)
    {
        $this->authorize('edit', $form);

        return Inertia::render('forms/connect/Index', [
            'form' => $form,
            'can' => $this->formAbilities($form),
            'connections' => FormConnection::all()->map(fn (Connection $connection): array => [
                'handle' => $connection->handle(),
                'title' => $connection->title(),
                'description' => $connection->description(),
                'icon' => $connection->icon(),
                'developer' => $connection->developer(),
                'count' => count($form->connections()->get($connection->handle(), [])),
                'url' => cp_route('forms.connect.show', [$form->handle(), $connection->handle()]),
            ])->values(),
        ]);
    }
        ]);
    }
}
