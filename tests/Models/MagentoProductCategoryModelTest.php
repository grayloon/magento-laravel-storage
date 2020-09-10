<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoProductCategoryFactory;

class MagentoProductCategoryModelTest extends TestCase
{
    public function test_can_create_magento_product_category()
    {
        $productCategory = MagentoProductCategoryFactory::new()->create();

        $this->assertNotEmpty($productCategory);
    }
}
