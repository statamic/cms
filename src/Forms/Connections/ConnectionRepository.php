<?php

namespace Statamic\Forms\Connections;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ConnectionRepository
{
    public function all(): Collection
    {
        return $this->classes()->map(fn ($class) => app($class));
    }

    public function find($handle): ?Connection
    {
        return ($class = $this->classes()->get($handle)) ? app($class) : null;
    }

    public function routes(): void
    {
        Route::namespace('\\')->prefix('forms/{form}/connect')->name('forms.connect.')->group(function () {
            $this->all()->each(function (Connection $connection) {
                Route::name($connection::handle().'.')
                    ->prefix($connection::handle())
                    ->middleware('can:edit,form')
                    ->group(fn () => $connection->routes(Route::getFacadeRoot()));
            });
        });
    }

    public function classes(): Collection
    {
        return app('statamic.form-connections');
    }
}
