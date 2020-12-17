<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Jobs\UpdateProductAttributeGroup;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Support\HasCustomAttributes;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class HasCustomAttributesTest extends TestCase
{
    public function test_resolves_new_custom_attribute_type()
    {
        Queue::fake();

        $newType = (new FakeSupportingClass)->exposedResolveCustomAttributeType('foo_bar');

        $this->assertNotEmpty($newType);
        $this->assertEquals('foo_bar', $newType->name);
        $this->assertEquals('Foo Bar', $newType->display_name);
        $this->assertIsArray($newType->options);
        $this->assertEmpty($newType->options);
        Queue::assertPushed(UpdateProductAttributeGroup::class, fn ($job) => $job->type->id === $newType->id);
    }

    public function test_resolves_existing_custom_attribute_type()
    {
        Queue::fake();
        $existing = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
        ]);

        $type = (new FakeSupportingClass)->exposedResolveCustomAttributeType('foo_bar');

        $this->assertNotEmpty($type);
        $this->assertEquals($type->id, $existing->id);
        $this->assertEquals(1, MagentoCustomAttributeType::count());
        Queue::assertNothingPushed();
    }

    public function test_updates_types_more_than_a_day_old()
    {
        Queue::fake();
        $existing = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'synced_at' => now()->subHours(25),
        ]);

        $type = (new FakeSupportingClass)->exposedResolveCustomAttributeType('foo_bar');

        $this->assertNotEmpty($type);
        $this->assertEquals($type->id, $existing->id);
        $this->assertEquals(1, MagentoCustomAttributeType::count());
        Queue::assertPushed(UpdateProductAttributeGroup::class);
    }

    public function test_updates_is_queued_when_type_set_to_sync()
    {
        Queue::fake();
        $existing = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'synced_at' => now()->subHours(25),
        ]);

        $type = (new FakeSupportingClass)->exposedResolveCustomAttributeType('foo_bar');

        $this->assertNotEmpty($type);
        $this->assertEquals($type->id, $existing->id);
        $this->assertEquals(1, MagentoCustomAttributeType::count());
        Queue::assertPushed(UpdateProductAttributeGroup::class);
        $this->assertTrue($type->is_queued);
    }

    public function test_updates_types_with_nullable_synced_at()
    {
        Queue::fake();
        $existing = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'synced_at' => null,
        ]);

        $type = (new FakeSupportingClass)->exposedResolveCustomAttributeType('foo_bar');

        $this->assertNotEmpty($type);
        $this->assertEquals($type->id, $existing->id);
        $this->assertEquals(1, MagentoCustomAttributeType::count());
        Queue::assertPushed(UpdateProductAttributeGroup::class);
    }

    public function test_doesnt_update_type_when_queued()
    {
        Queue::fake();
        $existing = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'is_queued' => true,
        ]);

        $type = (new FakeSupportingClass)->exposedResolveCustomAttributeType('foo_bar');

        $this->assertNotEmpty($type);
        $this->assertEquals($type->id, $existing->id);
        $this->assertEquals(1, MagentoCustomAttributeType::count());
        Queue::assertNothingPushed();
    }

    public function test_resolves_existing_raw_value_from_empty_options()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
        ]);

        $value = (new FakeSupportingClass)->exposedResolveCustomAttributeValue($type, 'foo');

        $this->assertEquals('foo', $value);
    }

    public function test_resolves_correct_value_from_provided_options()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'options' => [
                [
                    'label' => 'New York',
                    'value' => '1',
                ],
                [
                    'label' => 'Los Angeles',
                    'value' => '2',
                ],
            ],
        ]);

        $value = (new FakeSupportingClass)->exposedResolveCustomAttributeValue($type, '1');

        $this->assertEquals('New York', $value);
    }

    public function test_resolves_raw_value_from_option_not_supplied_in_options()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'options' => [
                [
                    'label' => 'New York',
                    'value' => '1',
                ],
                [
                    'label' => 'Los Angeles',
                    'value' => '2',
                ],
            ],
        ]);

        $value = (new FakeSupportingClass)->exposedResolveCustomAttributeValue($type, 'Unknown');

        $this->assertEquals('Unknown', $value);
    }

    public function test_updates_attribute_value_based_on_options()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'options' => [
                [
                    'label' => 'New York',
                    'value' => '1',
                ],
                [
                    'label' => 'Los Angeles',
                    'value' => '2',
                ],
            ],
        ]);

        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attribute_type_id' => $type->id,
            'value' => '1',
        ]);

        (new FakeSupportingClass)->exposedUpdateCustomAttributeTypeValues($type);

        $this->assertEquals('New York', $attribute->fresh()->value);
    }

    public function test_updates_multiple_attribute_value_based_on_options()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'options' => [
                [
                    'label' => 'New York',
                    'value' => '1',
                ],
                [
                    'label' => 'Los Angeles',
                    'value' => '2',
                ],
            ],
        ]);

        $attributes = MagentoCustomAttributeFactory::new()->count(10)->create([
            'attribute_type_id' => $type->id,
            'value' => '1',
        ]);

        (new FakeSupportingClass)->exposedUpdateCustomAttributeTypeValues($type);

        $this->assertEquals(10, $attributes->fresh()->where('value', 'New York')->count());
    }

    public function test_missing_option_keeps_raw_attribute_value()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'options' => [
                [
                    'label' => 'New York',
                    'value' => '1',
                ],
                [
                    'label' => 'Los Angeles',
                    'value' => '2',
                ],
            ],
        ]);

        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attribute_type_id' => $type->id,
            'value' => 'Unknown',
        ]);

        (new FakeSupportingClass)->exposedUpdateCustomAttributeTypeValues($type);

        $this->assertEquals('Unknown', $attribute->fresh()->value);
    }

    public function test_raw_attribute_value_is_resolvable()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo_bar',
            'options' => [],
        ]);

        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attribute_type_id' => $type->id,
            'value' => null,
        ]);

        (new FakeSupportingClass)->exposedUpdateCustomAttributeTypeValues($type);

        $this->assertNull($attribute->fresh()->value);
    }

    public function test_can_create_custom_attribute()
    {
        MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo',
            'options' => [['label' => 'bar', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $product = MagentoProductFactory::new()->create();

        $attributes = [
            [
                'attribute_code' => 'foo',
                'value'          => 2,
            ],
        ];

        (new FakeSupportingClass)->exposedSyncCustomAttributes($attributes, $product);

        $product->load('customAttributes');

        $this->assertNotNull($product->customAttributes);
        $this->assertEquals('bar', $product->customAttributes->first()->value);
    }

    public function test_updates_custom_attribute()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo',
            'options' => [['label' => 'bar', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $product = MagentoProductFactory::new()->create();
        MagentoCustomAttributeFactory::new()->create([
            'attribute_type'    => $type->name,
            'attribute_type_id' => $type->id,
            'value'             => 'foo',
            'attributable_id'   => $product->id,
            'attributable_type' => MagentoProduct::class,
            'synced_at'         => now(),
        ]);

        $attributes = [
            [
                'attribute_code' => 'foo',
                'value'          => 2,
            ],
        ];

        (new FakeSupportingClass)->exposedSyncCustomAttributes($attributes, $product);

        $product->load('customAttributes');

        $this->assertNotNull($product->customAttributes);
        $this->assertEquals(1, $product->customAttributes->count());
        $this->assertEquals('bar', $product->customAttributes->first()->value);
    }

    public function test_removes_missing_custom_attribute()
    {
        $missingType = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'bar',
            'options' => [['label' => 'baz', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo',
            'options' => [['label' => 'bar', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $product = MagentoProductFactory::new()->create();
        MagentoCustomAttributeFactory::new()->create([
            'attribute_type'    => $missingType->name,
            'attribute_type_id' => $missingType->id,
            'value'             => 'baz',
            'attributable_id'   => $product->id,
            'attributable_type' => MagentoProduct::class,
            'synced_at'         => now()->subMinutes(15),
        ]);

        $attributes = [
            [
                'attribute_code' => 'foo',
                'value'          => 2,
            ],
        ];

        (new FakeSupportingClass)->exposedSyncCustomAttributes($attributes, $product);

        $product->load('customAttributes');

        $this->assertNotNull($product->customAttributes);
        $this->assertEquals(1, $product->customAttributes->count());
        $this->assertEquals('bar', $product->customAttributes->first()->value);
    }

    public function test_removes_nullable_synced_at_custom_attribute()
    {
        $missingType = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'bar',
            'options' => [['label' => 'baz', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo',
            'options' => [['label' => 'bar', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $product = MagentoProductFactory::new()->create();
        MagentoCustomAttributeFactory::new()->create([
            'attribute_type'    => $missingType->name,
            'attribute_type_id' => $missingType->id,
            'value'             => 'baz',
            'attributable_id'   => $product->id,
            'attributable_type' => MagentoProduct::class,
            'synced_at'         => null,
        ]);

        $attributes = [
            [
                'attribute_code' => 'foo',
                'value'          => 2,
            ],
        ];

        (new FakeSupportingClass)->exposedSyncCustomAttributes($attributes, $product);

        $product->load('customAttributes');

        $this->assertNotNull($product->customAttributes);
        $this->assertEquals(1, $product->customAttributes->count());
        $this->assertEquals('bar', $product->customAttributes->first()->value);
    }

    public function test_doesnt_remove_out_of_sync_attributes_when_in_group()
    {
        $otherType = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'bar',
            'options' => [['label' => 'baz', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'foo',
            'options' => [['label' => 'bar', 'value' => 2]],
            'synced_at' => now(),
        ]);

        $product = MagentoProductFactory::new()->create();
        MagentoCustomAttributeFactory::new()->create([
            'attribute_type'    => $otherType->name,
            'attribute_type_id' => $otherType->id,
            'value'             => 'baz',
            'attributable_id'   => $product->id,
            'attributable_type' => MagentoProduct::class,
            'synced_at'         => now(),
        ]);

        $attributes = [
            [
                'attribute_code' => 'foo',
                'value'          => 2,
            ],
        ];

        (new FakeSupportingClass)->exposedSyncCustomAttributes($attributes, $product);

        $product->load('customAttributes');

        $this->assertNotNull($product->customAttributes);
        $this->assertEquals(2, $product->customAttributes->count());
        $this->assertEquals('baz', $product->customAttributes->first()->value);
    }
}

class FakeSupportingClass
{
    use HasCustomAttributes;

    public function exposedResolveCustomAttributeType($attributeCode)
    {
        return $this->resolveCustomAttributeType($attributeCode);
    }

    public function exposedResolveCustomAttributeValue($type, $value)
    {
        return $this->resolveCustomAttributeValue($type, $value);
    }

    public function exposedUpdateCustomAttributeTypeValues($type)
    {
        return $this->updateCustomAttributeTypeValues($type);
    }

    public function exposedSyncCustomAttributes($attributes, $model, $checkConditionalRules = false)
    {
        return $this->syncCustomAttributes($attributes, $model, $checkConditionalRules);
    }
}
