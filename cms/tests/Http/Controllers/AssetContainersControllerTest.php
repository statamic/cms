<?php namespace Tests;

class AssetContainersControllerTest extends TestCase
{
    /** @test **/
    public function testEmptyFieldsAreNotAllowedGivenALocalStorageDriver()
    {
        $this->withoutMiddleware();

        $data = [
            'title'  => 'Main Assets',
            'handle' => 'main-assets',
            'driver' => 'local',
            'local'  => []
        ];

        $this->json('POST', '/cp/configure/content/assets', $data)
            ->seeJson([
                'success' => false,
                'errors' => [
                    'The URL is required.',
                    'The path is required.',
                ]
            ]);
    }

    /** @test **/
    public function testEmptyFieldsAreNotAllowedGivenAnAmazonS3Driver()
    {
        $this->withoutMiddleware();

        $data = [
            'title'  => 'Main Assets',
            'handle' => 'main-assets',
            'driver' => 's3',
            'local'  => []
        ];

        $this->json('POST', '/cp/configure/content/assets', $data)
            ->seeJson([
                'success' => false,
                'errors' => [
                    'The access key ID is required.',
                    'The secret access key is required.',
                    'The bucket is required.',
                    'The region is required.',
                ]
            ]);
    }
}
