<?php

namespace Statamic\Forms\Connectors;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ConnectorRepository
{
    public function all(): Collection
    {
        return app('statamic.form-connectors')
            ->map(fn ($class) => app($class))
            ->filter()
            ->values();
    }

    public function find(string $handle): ?Connector
    {
        return ($class = app('statamic.form-connectors')->get($handle)) ? app($class) : null;
    }

    public function routes(): void
    {
        Route::prefix('forms/{form}/connect')->name('forms.connect.')->group(function () {
            $this->all()->each(function (Connector $connector) {
                Route::name($connector::handle().'.')
                    ->prefix($connector::handle())
                    ->middleware('can:edit,form')
                    ->group(fn () => $connector->routes(Route::getFacadeRoot()));
            });
        });
    }
}
