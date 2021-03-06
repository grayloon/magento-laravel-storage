<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductLinkFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductLink;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOption;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOptionValue;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttribute;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoProductMedia;
use Grayloon\MagentoStorage\Models\MagentoTierPrice;
use Grayloon\MagentoStorage\Support\MagentoProducts;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class MagentoProductsTest extends TestCase
{
    public function test_can_count_magento_products()
    {
        Http::fake(function ($request) {
            return Http::response([
                'total_count' => 1,
            ], 200);
        });

        $magentoProducts = new MagentoProducts();

        $count = $magentoProducts->count();

        $this->assertEquals(1, $count);
    }

    public function test_can_create_product()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::first();
        $this->assertNotEmpty($product);
        $this->assertEquals('Dunder Mifflin Paper', $product->name);
        $this->assertEquals('DMPC001', $product->sku);
        $this->assertEquals(250, $product->quantity);
        $this->assertNotEmpty($product->synced_at);
    }

    public function test_missing_stock_item_resolves_quantity()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $product = $this->fakeProduct([
            'extension_attributes' => [
                'website_id' => [1],
            ],
        ]);

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::first();
        $this->assertNotEmpty($product);
        $this->assertEquals(0, $product->quantity);
        $this->assertEquals(0, $product->is_in_stock);
    }

    public function test_can_add_extension_attributes()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::with('extensionAttributes', 'extensionAttributes.type')->first();

        $this->assertNotEmpty($product);
        $this->assertEquals(2, $product->extensionAttributes->count());
        $this->assertInstanceOf(MagentoExtensionAttribute::class, $product->extensionAttributes->first());
        $this->assertEquals($product->id, $product->extensionAttributes->first()->magento_product_id);
        $this->assertInstanceOf(MagentoExtensionAttributeType::class, $product->extensionAttributes->first()->type);
        $this->assertEquals([1], $product->extensionAttributes->first()->attribute);
        $this->assertEquals('website_id', $product->extensionAttributes->first()->type->type);
    }

    public function test_can_add_custom_attributes()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::with('customAttributes', 'customAttributes.type')->first();

        $this->assertNotEmpty($product);
        $this->assertNotEmpty($product->customAttributes->first());
        $this->assertInstanceOf(MagentoCustomAttribute::class, $product->customAttributes->first());
        $this->assertEquals('1', $product->customAttributes->first()->value);
        $this->assertInstanceOf(MagentoCustomAttributeType::class, $product->customAttributes->first()->type()->first());
        $this->assertEquals('Warehouse Id', $product->customAttributes->first()->type()->first()->display_name);
        $this->assertEquals('warehouse_id', $product->customAttributes->first()->type()->first()->name);
    }

    public function test_can_add_product_links()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();
        MagentoProductFactory::new()->create([
            'id' => 2,
            'sku' => 'bar',
        ]);

        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::with('related')->first();

        $this->assertNotEmpty($product);
        $this->assertNotEmpty($product->related);
        $this->assertEquals('bar', $product->related->first()->sku);
    }

    public function test_can_add_product_images()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::with('images')->first();

        $this->assertNotEmpty($product);
        $this->assertNotEmpty($product->images);
        $this->assertEquals(1, $product->images->count());
        $this->assertInstanceOf(MagentoProductMedia::class, $product->images->first());
        $this->assertEquals('/p/paper.jpg', $product->images->first()->file);
    }

    public function test_can_apply_rule_apply_slug()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::with('categories')->first();

        $this->assertNotEmpty($product);
        $this->assertEquals('paper-and-office-supplies', $product->slug);
    }

    public function test_deletes_if_exists()
    {
        $product = MagentoProductFactory::new()->create();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->deleteIfExists($product->sku);

        $this->assertNull($product->fresh());
    }

    public function test_can_add_stock_item_data_as_custom_attributes()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::with('customAttributes', 'customAttributes.type')->first();

        $this->assertNotEmpty($product);
        $minQty = $product->customAttributes->where('attribute_type', 'stock-item--min_qty')->first();
        $this->assertNotEmpty($minQty);
        $this->assertEquals('3', $minQty->value);
    }

    /** @test */
    public function it_can_sync_configurable_product()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();

        $fakeConfigurableProduct = $this->fakeConfigurableProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($fakeConfigurableProduct['product']);

        $this->assertEquals(1, MagentoConfigurableProductLink::count());
        $this->assertEquals(1, MagentoConfigurableProductOption::count());
        $this->assertEquals(1, MagentoConfigurableProductOptionValue::count());
    }

    /** @test */
    public function it_removes_old_configurable_product_data()
    {
        Queue::fake();
        MagentoCategoryFactory::new()->create();
        $configurableProduct = MagentoProductFactory::new()->create([
            'id' => 1,
            'sku' => 'DMPC001',
        ]);
        $link = MagentoConfigurableProductLinkFactory::new()->create([
            'configurable_product_id' => $configurableProduct->id,
        ]);

        $fakeConfigurableProduct = $this->fakeConfigurableProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($fakeConfigurableProduct['product']);

        $this->assertEquals(1, MagentoConfigurableProductLink::count());
        $this->assertNotEquals($link->id, MagentoConfigurableProductLink::first()->id);
    }

    /** @test */
    public function it_can_sync_price_tiers()
    {
        Queue::fake();
        $product = $this->fakeProduct();

        $magentoProducts = new MagentoProducts();
        $magentoProducts->updateOrCreateProduct($product);

        $product = MagentoProduct::with('tierPrices')->first();

        $this->assertNotEmpty($product);
        $this->assertNotEmpty($product->tierPrices);
        $this->assertInstanceOf(MagentoTierPrice::class, $product->tierPrices->first());
        $this->assertEquals(MagentoTierPrice::count(), 1);
    }

    protected function fakeProduct($attributes = null)
    {
        $product = [
            'id'         => '1',
            'name'       => 'Dunder Mifflin Paper',
            'sku'        => 'DMPC001',
            'price'      => 19.99,
            'status'     => '1',
            'visibility' => '1',
            'type_id'    => 'simple',
            'created_at' => now(),
            'updated_at' => now(),
            'weight'     => 10.00,
            'extension_attributes' => [
                'website_id' => [1],
                'stock_item' => [
                    'item_id' => 1,
                    'product_id' => 1,
                    'stock_id' => 1,
                    'qty' => 250,
                    'is_in_stock' => true,
                    'is_qty_decimal' => false,
                    'show_default_notification_message' => false,
                    'use_config_min_qty' => true,
                    'min_qty' => 3,
                    'use_config_min_sale_qty' => 1,
                    'min_sale_qty' => 1,
                    'use_config_max_sale_qty' => true,
                    'max_sale_qty' => 10000,
                    'use_config_backorders' => true,
                    'backorders' => 0,
                    'use_config_notify_stock_qty' => true,
                    'notify_stock_qty' => 0,
                    'use_config_qty_increments' => true,
                    'qty_increments' => 0,
                    'use_config_enable_qty_inc' => true,
                    'enable_qty_increments' => false,
                    'use_config_manage_stock' => false,
                    'manage_stock' => false,
                    'low_stock_date' => null,
                    'is_decimal_divided' => false,
                    'stock_status_changed_auto' => 0,
                    'extension_attributes' => [],
                ],
            ],
            'product_links' => [
                [
                    'sku' => 'DMPC001',
                    'link_type' => 'related',
                    'linked_product_sku' => 'bar',
                    'linked_product_type' => 'simple',
                    'position' => 0,
                ],
            ],
            'tier_prices' => [
                [
                    'customer_group_id' => 1,
                    'qty' => 1,
                    'value' => 99.99,
                    'extension_attributes' => [
                        'website_id' => 1,
                    ],
                ],
            ],
            'media_gallery_entries' => [
                [
                    'id' => 1,
                    'media_type' => 'image',
                    'label' => null,
                    'position' => 1,
                    'disabled' => false,
                    'types' => [
                        'image',
                        'small_image',
                        'thumbnail',
                    ],
                    'file' => '/p/paper.jpg',
                ],
            ],
            'custom_attributes' => [
                [
                    'attribute_code' => 'warehouse_id',
                    'value'          => '1',
                ],
                [
                    'attribute_code' => 'url_key',
                    'value'          => 'paper-and-office-supplies',
                ],
                [
                    'attribute_code' => 'category_ids',
                    'value'          => [
                        '1',
                    ],
                ],
            ],
        ];

        if ($attributes) {
            $product = array_merge($product, $attributes);
        }

        return $product;
    }

    protected function fakeConfigurableProduct()
    {
        $link = MagentoProductFactory::new()->create();
        $type = MagentoCustomAttributeTypeFactory::new()->create();

        $product = [
            'id'         => '1',
            'name'       => 'Dunder Mifflin Paper',
            'sku'        => 'DMPC001',
            'price'      => 19.99,
            'status'     => '1',
            'visibility' => '1',
            'type_id'    => 'configurable',
            'created_at' => now(),
            'updated_at' => now(),
            'weight'     => 10.00,
            'extension_attributes' => [
                'website_id' => [1],
                'stock_item' => [
                    'item_id' => 1,
                    'product_id' => 1,
                    'stock_id' => 1,
                    'qty' => 250,
                    'is_in_stock' => true,
                    'is_qty_decimal' => false,
                    'show_default_notification_message' => false,
                    'use_config_min_qty' => true,
                    'min_qty' => 3,
                    'use_config_min_sale_qty' => 1,
                    'min_sale_qty' => 1,
                    'use_config_max_sale_qty' => true,
                    'max_sale_qty' => 10000,
                    'use_config_backorders' => true,
                    'backorders' => 0,
                    'use_config_notify_stock_qty' => true,
                    'notify_stock_qty' => 0,
                    'use_config_qty_increments' => true,
                    'qty_increments' => 0,
                    'use_config_enable_qty_inc' => true,
                    'enable_qty_increments' => false,
                    'use_config_manage_stock' => false,
                    'manage_stock' => false,
                    'low_stock_date' => null,
                    'is_decimal_divided' => false,
                    'stock_status_changed_auto' => 0,
                    'extension_attributes' => [],
                ],
                'configurable_product_options' => [
                    [
                        'id' => 1,
                        'attribute_id' => $type->attribute_id,
                        'label' => 'Paper',
                        'position' => 0,
                        'product_id' => '1',
                        'values' => [
                            [
                                'value_index' => 1,
                            ],
                        ],
                    ],
                ],
                'configurable_product_links' => [
                    $link->id,
                ],
            ],
            'product_links' => [
                [
                    'sku' => 'DMPC001',
                    'link_type' => 'related',
                    'linked_product_sku' => 'bar',
                    'linked_product_type' => 'simple',
                    'position' => 0,
                ],
            ],
            'media_gallery_entries' => [
                [
                    'id' => 1,
                    'media_type' => 'image',
                    'label' => null,
                    'position' => 1,
                    'disabled' => false,
                    'types' => [
                        'image',
                        'small_image',
                        'thumbnail',
                    ],
                    'file' => '/p/paper.jpg',
                ],
            ],
            'custom_attributes' => [
                [
                    'attribute_code' => 'warehouse_id',
                    'value'          => '1',
                ],
                [
                    'attribute_code' => 'url_key',
                    'value'          => 'paper-and-office-supplies',
                ],
                [
                    'attribute_code' => 'category_ids',
                    'value'          => [
                        '1',
                    ],
                ],
            ],
        ];

        return collect([
            'product' => $product,
            'link'    => $link,
            'type'    => $type,
        ]);
    }
}
