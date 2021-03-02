<?php

use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductLinkFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionValueFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Support\HasConfigurableProducts;
use Grayloon\MagentoStorage\Tests\TestCase;

class HasConfigurableProductsTest extends TestCase
{
    use HasConfigurableProducts;

    /** @test */
    public function it_attaches_matching_products_to_option_values()
    {
        $configurableProduct = MagentoProductFactory::new()->create();
        $linkedProduct = MagentoProductFactory::new()->create();

        MagentoConfigurableProductLinkFactory::new()->create([
            'configurable_product_id' => $configurableProduct->id,
            'product_id' => $linkedProduct->id,
        ]);

        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'color',
            'options' => [
                [
                    'label' => 'Blue',
                    'value' => '1',
                ],
                [
                    'label' => 'Green',
                    'value' => '2',
                ],
            ],
        ]);

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type' => MagentoProduct::class,
            'attributable_id'   => $linkedProduct->id,
            'attribute_type_id' => $type->id,
            'attribute_type'    => $type->name,
            'value'             => 'Green',
        ]);

        $option = MagentoConfigurableProductOptionFactory::new()->create([
            'attribute_type_id'  => $type->attribute_id,
            'magento_product_id' => $configurableProduct->id,
            'label'              => 'Color',
        ]);

        MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => $option->id,
            'value' => 'Green',
        ]);

        $configurableProduct = $this->resolveConfigurableOptions($configurableProduct);

        $this->assertNotNull($configurableProduct->configurableProductOptions->first()->optionValues->first()->product);
        $this->assertEquals($linkedProduct->id, $configurableProduct->configurableProductOptions->first()->optionValues->first()->product->id);
    }

    /** @test */
    public function it_returns_early_product_on_configurable_product_without_links()
    {
        $configurableProduct = MagentoProductFactory::new()->create();

        $configurableProduct = $this->resolveConfigurableOptions($configurableProduct);

        $this->assertEmpty($configurableProduct->configurableLinks);
    }

    /** @test */
    public function it_returns_early_product_on_configurable_product_without_options()
    {
        $configurableProduct = MagentoProductFactory::new()->create();
        $linkedProduct = MagentoProductFactory::new()->create();
        MagentoConfigurableProductLinkFactory::new()->create([
            'configurable_product_id' => $configurableProduct->id,
            'product_id' => $linkedProduct->id,
        ]);

        $configurableProduct = $this->resolveConfigurableOptions($configurableProduct);

        $this->assertEmpty($configurableProduct->configurableProductOptions);
    }

    /** @test */
    public function it_removes_option_when_missing_option_values()
    {
        $configurableProduct = MagentoProductFactory::new()->create();
        $linkedProduct = MagentoProductFactory::new()->create();

        MagentoConfigurableProductLinkFactory::new()->create([
            'configurable_product_id' => $configurableProduct->id,
            'product_id' => $linkedProduct->id,
        ]);

        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'color',
            'options' => [
                [
                    'label' => 'Blue',
                    'value' => '1',
                ],
                [
                    'label' => 'Green',
                    'value' => '2',
                ],
            ],
        ]);

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type' => MagentoProduct::class,
            'attributable_id'   => $linkedProduct->id,
            'attribute_type_id' => $type->id,
            'attribute_type'    => $type->name,
            'value'             => 'Green',
        ]);

        MagentoConfigurableProductOptionFactory::new()->create([
            'attribute_type_id'  => $type->attribute_id,
            'magento_product_id' => $configurableProduct->id,
            'label'              => 'Color',
        ]);

        $configurableProduct = $this->resolveConfigurableOptions($configurableProduct);

        $this->assertTrue($configurableProduct->configurableProductOptions->isEmpty());
    }

    /** @test */
    public function it_removes_option_value_when_missing_attached_product()
    {
        $configurableProduct = MagentoProductFactory::new()->create();
        $linkedProduct = MagentoProductFactory::new()->create();

        MagentoConfigurableProductLinkFactory::new()->create([
            'configurable_product_id' => $configurableProduct->id,
            'product_id' => $linkedProduct->id,
        ]);

        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'color',
            'options' => [
                [
                    'label' => 'Blue',
                    'value' => '1',
                ],
                [
                    'label' => 'Green',
                    'value' => '2',
                ],
            ],
        ]);

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type' => MagentoProduct::class,
            'attributable_id'   => $linkedProduct->id,
            'attribute_type_id' => $type->id,
            'attribute_type'    => $type->name,
            'value'             => 'Green',
        ]);

        $option = MagentoConfigurableProductOptionFactory::new()->create([
            'attribute_type_id'  => $type->attribute_id,
            'magento_product_id' => $configurableProduct->id,
            'label'              => 'Color',
        ]);

        MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => $option->id,
            'value' => 'Green',
        ]);

        MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => $option->id,
            'value' => 'Blue',
        ]);

        $configurableProduct = $this->resolveConfigurableOptions($configurableProduct);

        $this->assertEquals(1, $configurableProduct->configurableProductOptions->first()->optionValues->count(1));
    }

    /** @test */
    public function it_removes_option_when_no_products_match_all_values()
    {
        $configurableProduct = MagentoProductFactory::new()->create();
        $linkedProduct = MagentoProductFactory::new()->create();

        MagentoConfigurableProductLinkFactory::new()->create([
            'configurable_product_id' => $configurableProduct->id,
            'product_id' => $linkedProduct->id,
        ]);

        $type = MagentoCustomAttributeTypeFactory::new()->create([
            'name' => 'color',
            'options' => [
                [
                    'label' => 'Blue',
                    'value' => '1',
                ],
                [
                    'label' => 'Green',
                    'value' => '2',
                ],
            ],
        ]);

        $option = MagentoConfigurableProductOptionFactory::new()->create([
            'attribute_type_id'  => $type->attribute_id,
            'magento_product_id' => $configurableProduct->id,
            'label'              => 'Color',
        ]);

        MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => $option->id,
            'value' => 'Green',
        ]);

        MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => $option->id,
            'value' => 'Blue',
        ]);

        $configurableProduct = $this->resolveConfigurableOptions($configurableProduct);

        $this->assertTrue($configurableProduct->configurableProductOptions->isEmpty());
    }
}
