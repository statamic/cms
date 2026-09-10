<?php

namespace Statamic\Forms\Connections;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ConnectionRepository
{
    public function all(): Collection
    {
        return app('statamic.form-connections')
            ->map(fn ($class) => app($class))
            ->filter()
            ->values();
    }

    public function find(string $handle): ?Connection
    {
        return ($class = app('statamic.form-connections')->get($handle)) ? app($class) : null;
    }

    public function routes(): void
    {
        Route::prefix('forms/{form}/connect')->name('forms.connect.')->group(function () {
            $this->all()->each(function (Connection $connection) {
                Route::name($connection::handle().'.')
                    ->prefix($connection::handle())
                    ->middleware('can:edit,form')
                    ->group(fn () => $connection->routes(Route::getFacadeRoot()));
            });
        });
    }
}
