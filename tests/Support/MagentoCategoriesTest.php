<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Jobs\DownloadMagentoCategoryImage;
use Grayloon\MagentoStorage\Jobs\UpdateProductAttributeGroup;
use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Support\MagentoCategories;
use Grayloon\MagentoStorage\Tests\TestCase;
use function GuzzleHttp\Promise\queue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class MagentoCategoriesTest extends TestCase
{
    public function test_can_count_magento_categories()
    {
        Http::fake(function ($request) {
            return Http::response([
                'total_count' => 1,
            ], 200);
        });

        $magentoCategories = new MagentoCategories();

        $count = $magentoCategories->count();

        $this->assertEquals(1, $count);
    }

    public function test_can_create_magento_category()
    {
        Queue::fake();

        $categories = [
            [
                'id'         => '1',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'url_path',
                        'value' => 'foo/bar',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                ],
            ],
        ];

        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'path']);
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'url_path']);
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'children_count']);

        (new MagentoCategories())->updateCategories($categories);

        $category = MagentoCategory::first();

        $this->assertNotEmpty($category);
        $this->assertEquals('Root Catalog', $category->name);
        $this->assertNull($category->parent_id);
        $this->assertEquals(3, $category->customAttributes()->count());
        $this->assertEquals('path', $category->customAttributes()->first()->attribute_type);
        $this->assertEquals('1', $category->customAttributes()->first()->value);
        $this->assertEquals('foo/bar', $category->slug);
        Queue::assertNothingPushed();
    }

    public function test_root_category_has_nullable_slug()
    {
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'path']);
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'children_count']);

        $categories = [
            [
                'id'         => '1',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $category = MagentoCategory::first();

        $this->assertNotEmpty($category);
        $this->assertNull($category->slug);
    }

    public function test_can_apply_new_custom_attribute_type_to_category()
    {
        Queue::fake();

        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'path']);
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'children_count']);

        $categories = [
            [
                'id'         => '1',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'warehouse_id',
                        'value' => '1',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $category = MagentoCategory::first();

        $this->assertEquals(3, $category->customAttributes()->count());
        $this->assertEquals('1', $category->customAttributes()->where('attribute_type', 'warehouse_id')->first()->value);
        Queue::assertPushed(UpdateProductAttributeGroup::class);
    }

    public function test_can_apply_raw_value_attribute_if_unknown_type_option_in_category()
    {
        Queue::fake();

        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'path']);
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'children_count']);

        $categories = [
            [
                'id'         => '1',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'warehouse_id',
                        'value' => 'Unknown',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $category = MagentoCategory::first();

        $this->assertEquals(3, $category->customAttributes()->count());
        $this->assertEquals('Unknown', $category->customAttributes()->where('attribute_type', 'warehouse_id')->first()->value);
        Queue::assertPushed(UpdateProductAttributeGroup::class);
    }

    public function test_can_download_category_image()
    {
        Queue::fake();

        $categories = [
            [
                'id'         => '1',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'url_path',
                        'value' => 'foo/bar',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'image',
                        'value' => 'pub/media/catalog/category/foo.jpg',
                    ],
                ],
            ],
        ];

        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'path']);
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'url_path']);
        MagentoCustomAttributeTypeFactory::new()->create(['name' => 'children_count']);

        (new MagentoCategories())->updateCategories($categories);

        $category = MagentoCategory::first();

        $this->assertNotEmpty($category);
        $this->assertEquals('Root Catalog', $category->name);
        $this->assertNull($category->parent_id);
        $this->assertEquals(4, $category->customAttributes()->count());
        $this->assertEquals('path', $category->customAttributes()->first()->attribute_type);
        $this->assertEquals('1', $category->customAttributes()->first()->value);
        $this->assertEquals('foo/bar', $category->slug);
        Queue::assertPushed(DownloadMagentoCategoryImage::class);
        Queue::assertPushed(DownloadMagentoCategoryImage::class, fn ($job) => $job->path === 'pub/media/catalog/category/foo.jpg');
        Queue::assertPushed(DownloadMagentoCategoryImage::class, fn ($job) => $job->category->id === $category->id);
    }

    /** @test */
    public function it_deletes_old_category()
    {
        Queue::fake();
        putenv('MAGENTO_DEFAULT_CATEGORY=3');

        $category = MagentoCategoryFactory::new()->create([
            'id' => 2,
        ]);
        
        $categories = [
            [
                'id'         => '2',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'url_path',
                        'value' => 'foo/bar',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'image',
                        'value' => 'pub/media/catalog/category/foo.jpg',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $this->assertEquals(0, MagentoCategory::count());
        $this->assertDeleted($category);
    }

    /** @test */
    public function it_ignores_non_root_config_level_categories()
    {
        Queue::fake();
        
        putenv('MAGENTO_DEFAULT_CATEGORY=3');
        
        $categories = [
            [
                'id'         => '1',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'url_path',
                        'value' => 'foo/bar',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'image',
                        'value' => 'pub/media/catalog/category/foo.jpg',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $this->assertEquals(0, MagentoCategory::count());
    }

    /** @test */
    public function it_ignores_category_without_root_path()
    {
        Queue::fake();
        
        putenv('MAGENTO_DEFAULT_CATEGORY=3');
        
        $categories = [
            [
                'id'         => '5',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1/2/4',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'url_path',
                        'value' => 'foo/bar',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'image',
                        'value' => 'pub/media/catalog/category/foo.jpg',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $this->assertEquals(0, MagentoCategory::count());
    }

    /** @test */
    public function it_allows_root_level_category()
    {
        Queue::fake();
        
        putenv('MAGENTO_DEFAULT_CATEGORY=2');
        
        $categories = [
            [
                'id'         => '2',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'url_path',
                        'value' => 'foo/bar',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'image',
                        'value' => 'pub/media/catalog/category/foo.jpg',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $this->assertEquals(1, MagentoCategory::count());
        $this->assertEquals(2, MagentoCategory::first()->id);
    }

    /** @test */
    public function it_allows_nested_level_category()
    {
        Queue::fake();
        
        putenv('MAGENTO_DEFAULT_CATEGORY=2');
        
        $categories = [
            [
                'id'         => '10',
                'parent_id'  => 0,
                'name'       => 'Root Catalog',
                'is_active'  => true,
                'position'   => 0,
                'level'      => 0,
                'children'   => '2',
                'created_at' => '2014-04-04 14:17:29',
                'updated_at' => '2014-04-04 14:17:29',
                'path'       => '1/2/9/10',
                'available_sort_by' => [],
                'include_in_menu' =>  true,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'path',
                        'value' => '1',
                    ],
                    [
                        'attribute_code' => 'url_path',
                        'value' => 'foo/bar',
                    ],
                    [
                        'attribute_code' => 'children_count',
                        'value' => '124',
                    ],
                    [
                        'attribute_code' => 'image',
                        'value' => 'pub/media/catalog/category/foo.jpg',
                    ],
                ],
            ],
        ];

        (new MagentoCategories())->updateCategories($categories);

        $this->assertEquals(1, MagentoCategory::count());
        $this->assertEquals(10, MagentoCategory::first()->id);
    }
}
