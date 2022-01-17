<?php

namespace Statamic\Tokens;

use Statamic\Facades\File;
use Statamic\Facades\YAML;

class TokenRepository
{
    public function make(?string $token, string $handler, array $data = []): Token
    {
        return new Token($token, $handler, $data);
    }

    public function find(string $token)
    {
        $path = storage_path('statamic/tokens/'.$token.'.yaml');

        if (! File::exists($path)) {
            return null;
        }

        $yaml = YAML::file($path)->parse();

        return $this->make($token, $yaml['handler'], $yaml['data']);
    }

    public function save(Token $token)
    {
        File::put(storage_path('statamic/tokens/'.$token->token().'.yaml'), $token->fileContents());

        return true;
    }

    public function delete(Token $token)
    {
        File::delete(storage_path('statamic/tokens/'.$token->token().'.yaml'));

        return true;
    }
}
