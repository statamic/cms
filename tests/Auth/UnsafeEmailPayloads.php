<?php

namespace Tests\Auth;

trait UnsafeEmailPayloads
{
    public static function unsafeEmailProvider(): array
    {
        return [
            'subdirectory' => ['zz/a@x.com'],
            'leading slash' => ['/a@x.com'],
            'quoted traversal' => ['"x/../../p"@x.com'],
            'comment traversal' => ['(x/../..)y@x.com'],
            'domain literal traversal' => ['a@[x/../../y]'],
            'backslash' => ['"x\\y"@x.com'],
            'nul' => ["a\0b@x.com"],
        ];
    }

    private function filesystemSnapshot(): array
    {
        $paths = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(dirname($this->fakeStacheDirectory), \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $paths[] = $file->getPathname();
        }

        sort($paths);

        return $paths;
    }
}
