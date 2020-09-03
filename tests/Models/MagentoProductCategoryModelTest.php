<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Models\MagentoProductCategory;

class MagentoProductCategoryModelTest extends TestCase
{
    public function test_can_create_magento_product_category()
    {
        $productCategory = factory(MagentoProductCategory::class)->create();

        $this->assertNotEmpty($productCategory);
    }
}
