<?php

namespace Statamic\Tokens;

use Statamic\Data\ExistsAsFile;

class Token extends AbstractToken
{
    use ExistsAsFile;

    public function path()
    {
        return storage_path('statamic/tokens/'.$this->token().'.yaml');
    }

    public function fileData()
    {
        return [
            'handler' => $this->handler,
            'data' => $this->data->all(),
        ];
    }
}
