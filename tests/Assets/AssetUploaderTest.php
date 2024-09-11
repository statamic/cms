<?php

namespace Tests\Assets;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetUploader;
use Tests\TestCase;

class AssetUploaderTest extends TestCase
{
    #[Test]
    #[DataProvider('filenameReplacementsProvider')]
    public function it_gets_safe_filename($originalFilename, $expectedFilename)
    {
        $this->assertEquals($expectedFilename, AssetUploader::getSafeFilename($originalFilename));
    }

    public static function filenameReplacementsProvider()
    {
        return [
            'spaces' => ['one two.jpg', 'one-two.jpg'],
            'hashes' => ['one#two.jpg', 'one-two.jpg'],
            'colons' => ['one:two.jpg', 'one-two.jpg'],
            'lt' => ['one<two.jpg', 'one-two.jpg'],
            'gt' => ['one>two.jpg', 'one-two.jpg'],
            'double quotes' => ['one"two.jpg', 'one-two.jpg'],
            'forward slashes' => ['one/two.jpg', 'one-two.jpg'],
            'backslashes' => ['one\\two.jpg', 'one-two.jpg'],
            'pipes' => ['one|two.jpg', 'one-two.jpg'],
            'question marks' => ['one?two.jpg', 'one-two.jpg'],
            'asterisks' => ['one*two.jpg', 'one-two.jpg'],
            'percentage' => ['one%two.jpg', 'one-two.jpg'],
            'ascii' => ['fòô-bàř', 'foo-bar'],
        ];
    }
}
