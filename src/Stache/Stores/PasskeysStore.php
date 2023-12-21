<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Passkey;
use Statamic\Facades\YAML;

class PasskeysStore extends BasicStore
{
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
