<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Jobs\UpdateProductAttributeGroup;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
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
}
