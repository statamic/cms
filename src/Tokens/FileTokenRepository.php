<?php

namespace Statamic\Tokens;

use Illuminate\Support\Carbon;
use Statamic\Contracts\Tokens\Token as TokenContract;
use Statamic\Facades\File;
use Statamic\Facades\YAML;

class FileTokenRepository extends TokenRepository
{
    public function make(?string $token, string $handler, array $data = []): TokenContract
    {
        return app()->makeWith(TokenContract::class, compact('token', 'handler', 'data'));
    }

    public function find(string $token): ?TokenContract
    {
        $path = storage_path('statamic/tokens/'.$token.'.yaml');

        if (! File::exists($path)) {
            return null;
        }

        return $this->makeFromPath($path);
    }

    public function save(TokenContract $token): bool
    {
        File::put(storage_path('statamic/tokens/'.$token->token().'.yaml'), $token->fileContents());

        return true;
    }

    public function delete(TokenContract $token): bool
    {
        File::delete(storage_path('statamic/tokens/'.$token->token().'.yaml'));

        return true;
    }

    public function collectGarbage(): void
    {
        File::getFilesByType(storage_path('statamic/tokens'), 'yaml')
            ->map(fn ($path) => $this->makeFromPath($path))
            ->filter->hasExpired()
            ->each->delete();
    }

    private function makeFromPath(string $path): FileToken
    {
        $yaml = YAML::file($path)->parse();

        $token = basename($path, '.yaml');

        return $this
            ->make($token, $yaml['handler'], $yaml['data'] ?? [])
            ->expireAt(Carbon::createFromTimestamp($yaml['expires_at']));
    }

    public static function bindings(): array
    {
        return [
            TokenContract::class => FileToken::class,
        ];
    }
}
