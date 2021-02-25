<?php

use Exception;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeFactory;
use Grayloon\MagentoStorage\Models\MagentoProductAttribute;
use Grayloon\MagentoStorage\Support\MagentoProductAttributes;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class MagentoProductAttributesTest extends TestCase
{
    public function test_can_count_magento_product_attributes()
    {
        Http::fake(fn () => Http::response(['total_count' => 1], 200));

        $this->assertEquals(1, (new MagentoProductAttributes)->count());
    }

    public function test_throws_error_on_bad_api()
    {
        $this->expectException(Exception::class);

        Http::fake(fn () => Http::response(['message' => 'Something bad happened.'], 403));

        (new MagentoProductAttributes)->count();
    }

    public function test_throws_error_on_missing_count_key()
    {
        $this->expectException(Exception::class);

        Http::fake(fn () => Http::response([], 200));

        (new MagentoProductAttributes)->count();
    }

    public function test_creates_magento_product_attribute_from_api()
    {
        $attribute = (new MagentoProductAttributes())->updateOrCreate([
            'attribute_id'    => 300,
            'frontend_labels' => [
                [
                    'store_id' => 1,
                    'label'    => 'foo',
                ],
                [
                    'store_id' => 2,
                    'label'    => 'bar',
                ],
            ],
            'default_frontend_label' => 'foo',
            'attribute_code'         => 'test_attribute',
            'position'               => 4,
            'default_value'          => '',
            'frontend_input'         => 'select',
        ]);

        $this->assertEquals(1, MagentoProductAttribute::count());

        $this->assertEquals(300, $attribute->id);
        $this->assertEquals('foo', $attribute->name);
        $this->assertEquals('test_attribute', $attribute->code);
        $this->assertEquals(4, $attribute->position);
        $this->assertEmpty($attribute->default_value);
        $this->assertEquals('select', $attribute->type);
    }

    public function test_updates_magento_product_attribute_from_api()
    {
        $existingAttribute = MagentoProductAttributeFactory::new()->create([
            'id' => 300,
        ]);
        $attribute = (new MagentoProductAttributes())->updateOrCreate([
            'attribute_id'    => 300,
            'frontend_labels' => [
                [
                    'store_id' => 1,
                    'label'    => 'foo',
                ],
                [
                    'store_id' => 2,
                    'label'    => 'bar',
                ],
            ],
            'default_frontend_label' => 'foo',
            'attribute_code'         => 'test_attribute',
            'position'               => 4,
            'default_value'          => '',
            'frontend_input'         => 'select',
        ]);

        $this->assertEquals(1, MagentoProductAttribute::count());

        $this->assertEquals(300, $attribute->id);
        $this->assertEquals('foo', $attribute->name);
        $this->assertEquals('test_attribute', $attribute->code);
        $this->assertEquals(4, $attribute->position);
        $this->assertEmpty($attribute->default_value);
        $this->assertEquals('select', $attribute->type);
    }

    public function test_resolves_attribute_label_without_config_without_available_labels_with_default()
    {
        $label = (new MagentoProductAttributes)->resolveAttributeLabel([], 'foo');

        $this->assertEquals('foo', $label);
    }

    public function test_resolves_attribute_label_with_config_without_available_labels_with_default()
    {
        config(['magento.default_store_id' => 1]);

        $label = (new MagentoProductAttributes)->resolveAttributeLabel([], 'foo');

        $this->assertEquals('foo', $label);
    }

    public function test_resolves_attribute_label_without_config_with_available_labels_without_default()
    {
        $label = (new MagentoProductAttributes)->resolveAttributeLabel([
            [
                'store_id' => 1,
                'label'    => 'foo',
            ],
            [
                'store_id' => 2,
                'label'    => 'bar',
            ],
        ], '');

        $this->assertEquals('foo', $label);
    }

    public function test_resolves_attribute_label_with_config_with_available_labels_with_default()
    {
        config(['magento.default_store_id' => 2]);

        $label = (new MagentoProductAttributes)->resolveAttributeLabel([
            [
                'store_id' => 1,
                'label'    => 'foo',
            ],
            [
                'store_id' => 2,
                'label'    => 'bar',
            ],
        ], 'foo');

        $this->assertEquals('bar', $label);
    }

    public function test_resolves_attribute_label_without_config_with_available_labels_with_default()
    {
        $label = (new MagentoProductAttributes)->resolveAttributeLabel([
            [
                'store_id' => 1,
                'label'    => 'foo',
            ],
            [
                'store_id' => 2,
                'label'    => 'bar',
            ],
        ], 'bar');

        $this->assertEquals('bar', $label);
    }
}
