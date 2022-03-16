<?php

namespace Statamic\Tokens;

use Illuminate\Support\Carbon;
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

        return $this->makeFromPath($path);
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

    public function collectGarbage()
    {
        File::getFilesByType(storage_path('statamic/tokens'), 'yaml')
            ->map(fn ($path) => $this->makeFromPath($path))
            ->filter->hasExpired()
            ->each->delete();
    }

    private function makeFromPath(string $path): Token
    {
        $yaml = YAML::file($path)->parse();

        $token = basename($path, '.yaml');

        return $this
            ->make($token, $yaml['handler'], $yaml['data'] ?? [])
            ->expireAt(Carbon::createFromTimestamp($yaml['expires_at']));
    }
}
