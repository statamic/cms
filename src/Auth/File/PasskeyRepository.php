<?php

namespace Statamic\Auth\File;

use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Contracts\Auth\PasskeyRepository as RepositoryContract;
use Statamic\Stache\Stache;

class PasskeyRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('passkeys');
    }

    public function all(): Collection
    {
        $keys = $this->store->paths()->keys();

        return $this->store->getItems($keys);
    }

    public function find($id): ?Passkey
    {
        return $this->store->getItem($id);
    }

    public function make(): Passkey
    {
        return app(Passkey::class);
    }

    public function save(Passkey $passkey)
    {
        $this->store->save($passkey);
    }

    public function delete(Passkey $passkey)
    {
        $this->store->delete($passkey);
    }

    public static function bindings(): array
    {
        return [
            Passkey::class => \Statamic\Auth\File\Passkey::class,
        ];
    }
}
