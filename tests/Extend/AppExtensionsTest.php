<?php

namespace Tests\Extend;

use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;
use Statamic\Providers\ExtensionServiceProvider;
use Tests\TestCase;

class AppExtensionsTest extends TestCase
{
    #[Test]
    public function it_resolves_namespaces_correctly()
    {
        $files = collect($this->app['files']->allFiles(__DIR__.'/../__fixtures__/classes/app'))
            ->map(fn (SplFileInfo $file) => [$file, trim(file_get_contents($file->getPathname()))]);

        $basePaths = [
            __DIR__.'/../__fixtures__/classes/app',
            __DIR__.'/../__fixtures__/classes/app\\',
            __DIR__.'/../__fixtures__/classes/app/',
            __DIR__.'/..\\__fixtures__/classes/app/',
        ];

        $appNamespaces = [
            'App' => [
                'App',
                'App\\',
                'App////',
                'App/\\',
                '\\App/',
                '\\App/\\',
            ],
            'Something\\Nested\\Here' => [
                'Something\\Nested\\Here',
                'Something\\Nested\\Here\\',
                'Something\\Nested\\Here/',
                'Something\\Nested\\Here/\\',
                '\\Something\\Nested\\Here/',
                '\\Something\\Nested\\Here/\\',
            ],
        ];

        foreach ($files as $info) {
            [$file, $expectedRelativeClassName] = $info;

            foreach ($appNamespaces as $appNamespace => $appVariations) {
                $expectedNamespace = "{$appNamespace}\\{$expectedRelativeClassName}";

                foreach ($basePaths as $basePath) {
                    foreach ($appVariations as $variation) {
                        $this->assertSame(
                            $expectedNamespace,
                            ExtensionServiceProvider::getNamespaceFromFile($file, $basePath, $variation)
                        );
                    }
                }
            }
        }
    }
}
