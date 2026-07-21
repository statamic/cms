<?php

namespace Statamic\Forms\Connections;

use Facades\Statamic\Forms\Connections\CoreConnections;
use Illuminate\Support\Facades\Route;

class ConnectionRepository
{
    protected $connections;
    protected $extensions = [];

    public function __construct()
    {
        $this->connections = collect([]);
    }

    public function boot()
    {
        CoreConnections::boot();

        foreach ($this->extensions as $callback) {
            $callback($this);
        }

        return $this;
    }

    public function extend($callback)
    {
        $this->extensions[] = $callback;
    }

    public function make($handle)
    {
        return (new Connection)->handle($handle);
    }

    public function register($connection)
    {
        if (! $connection instanceof Connection) {
            $connection = $this->make($connection);
        }

        $this->connections[$connection->handle()] = $connection;

        return $connection;
    }

    public function all()
    {
        return $this->connections;
    }

    public function find($handle)
    {
        return $this->all()->get($handle);
    }

    public function routes()
    {
        Route::namespace('\\')->prefix('forms/{form}/connect')->name('forms.connect.')->group(function () {
            $this->boot()->all()->each(function ($connection) {
                if ($routeClosure = $connection->routes()) {
                    Route::name($connection->handle().'.')
                        ->prefix($connection->handle())
                        ->middleware('can:edit,form')
                        ->group(function () use ($routeClosure) {
                            $routeClosure(Route::getFacadeRoot());
                        });
                }
            });
        });
    }
}
