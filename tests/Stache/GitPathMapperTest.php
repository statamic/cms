<?php

namespace Tests\Stache;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Stache\GitPathMapper;
use Tests\TestCase;

class GitPathMapperTest extends TestCase
{
    private GitPathMapper $mapper;

    private string $basePath;

    private array $storeDirectories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new GitPathMapper;
        $this->basePath = '/var/www/site';
        $this->storeDirectories = [
            'taxonomies' => '/var/www/site/content/taxonomies',
            'terms' => '/var/www/site/content/taxonomies',
            'collections' => '/var/www/site/content/collections',
            'entries' => '/var/www/site/content/collections',
            'navigation' => '/var/www/site/content/navigation',
            'collection-trees' => '/var/www/site/content/trees/collections',
            'nav-trees' => '/var/www/site/content/trees/navigation',
            'globals' => '/var/www/site/content/globals',
            'global-variables' => '/var/www/site/content/globals',
            'asset-containers' => '/var/www/site/content/assets',
            'users' => '/var/www/site/users',
        ];
    }

    private function map(array $changes): Collection
    {
        return $this->mapper->map(collect($changes), $this->basePath, $this->storeDirectories);
    }

    #[Test]
    public function it_maps_an_added_entry_to_update_item()
    {
        $actions = $this->map([
            ['status' => 'A', 'path' => 'content/collections/blog/my-post.md'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('entries::blog', $actions[0]['storeKey']);
        $this->assertEquals('/var/www/site/content/collections/blog/my-post.md', $actions[0]['absolutePath']);
        $this->assertEquals('content/collections/blog/my-post.md', $actions[0]['displayPath']);
    }

    #[Test]
    public function it_maps_a_modified_entry_to_update_item()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/collections/blog/my-post.md'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('entries::blog', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_deleted_entry_to_forget_item()
    {
        $actions = $this->map([
            ['status' => 'D', 'path' => 'content/collections/blog/my-post.md'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('forget-item', $actions[0]['type']);
        $this->assertEquals('entries::blog', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_collection_config_yaml_to_collections_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/collections/blog.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('collections', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_taxonomy_config_yaml_to_taxonomies_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/taxonomies/tags.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('taxonomies', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_term_to_terms_store()
    {
        $actions = $this->map([
            ['status' => 'A', 'path' => 'content/taxonomies/tags/laravel.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('terms::tags', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_deleted_term_to_forget_item()
    {
        $actions = $this->map([
            ['status' => 'D', 'path' => 'content/taxonomies/tags/laravel.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('forget-item', $actions[0]['type']);
        $this->assertEquals('terms::tags', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_global_config_to_globals_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/globals/settings.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('globals', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_global_variables_to_global_variables_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/globals/en/settings.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('global-variables', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_navigation_to_navigation_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/navigation/main.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('navigation', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_collection_tree_to_collection_trees_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/trees/collections/pages.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('collection-trees', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_nav_tree_to_nav_trees_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/trees/navigation/main.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('nav-trees', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_asset_container_yaml_to_asset_containers_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/assets/main.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('asset-containers', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_user_to_users_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'users/john@example.com.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('users', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_collection_blueprint_change_to_warm_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'resources/blueprints/collections/blog/article.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('warm-store', $actions[0]['type']);
        $this->assertEquals('entries::blog', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_a_taxonomy_blueprint_change_to_warm_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'resources/blueprints/taxonomies/tags/default.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('warm-store', $actions[0]['type']);
        $this->assertEquals('terms::tags', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_an_assets_blueprint_change_to_warm_store()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'resources/blueprints/assets/main.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('warm-store', $actions[0]['type']);
        $this->assertEquals('asset-containers', $actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_other_blueprint_changes_to_full_refresh()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'resources/blueprints/user.yaml'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('full-refresh', $actions[0]['type']);
        $this->assertNull($actions[0]['storeKey']);
    }

    #[Test]
    public function it_maps_unrecognized_files_to_full_refresh()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'resources/views/some-template.antlers.html'],
        ]);

        $this->assertCount(1, $actions);
        $this->assertEquals('full-refresh', $actions[0]['type']);
    }

    #[Test]
    public function it_maps_multiple_changes_in_one_call()
    {
        $actions = $this->map([
            ['status' => 'M', 'path' => 'content/collections/blog/post-1.md'],
            ['status' => 'D', 'path' => 'content/collections/blog/post-2.md'],
            ['status' => 'A', 'path' => 'content/collections/news/article-1.md'],
        ]);

        $this->assertCount(3, $actions);

        $this->assertEquals('update-item', $actions[0]['type']);
        $this->assertEquals('entries::blog', $actions[0]['storeKey']);

        $this->assertEquals('forget-item', $actions[1]['type']);
        $this->assertEquals('entries::blog', $actions[1]['storeKey']);

        $this->assertEquals('update-item', $actions[2]['type']);
        $this->assertEquals('entries::news', $actions[2]['storeKey']);
    }

    #[Test]
    public function it_returns_empty_collection_for_empty_changes()
    {
        $actions = $this->map([]);

        $this->assertCount(0, $actions);
    }

    #[Test]
    public function base_path_trailing_slash_is_normalized()
    {
        $actions = $this->mapper->map(
            collect([['status' => 'M', 'path' => 'content/collections/blog/post.md']]),
            '/var/www/site/',  // trailing slash
            $this->storeDirectories
        );

        $this->assertCount(1, $actions);
        $this->assertEquals('update-item', $actions[0]['type']);
    }
}
