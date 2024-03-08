<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Passkey;
use Statamic\Facades\YAML;
use Statamic\Stache\Indexes;

class PasskeysStore extends BasicStore
{
    protected $storeIndexes = [
        'user' => Indexes\Users\User::class,
    ];

    public function key()
    {
        return 'passkeys';
    }

    public function makeItemFromFile($path, $contents)
    {
        $id = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        return Passkey::make()
            ->id($id)
            ->user($data['user'])
            ->data($data);
    }
}
