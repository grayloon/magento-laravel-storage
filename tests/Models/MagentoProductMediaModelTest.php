<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductMediaFactory;
use Grayloon\MagentoStorage\Models\MagentoProduct;

class MagentoProductMediaModelTest extends TestCase
{
    public function test_can_create_media_record()
    {
        $this->assertNotEmpty(MagentoProductMediaFactory::new()->create());
    }

    public function test_magento_product_media_product_id_belongs_to_product()
    {
        $product = MagentoProductFactory::new()->create();
        $media = MagentoProductMediaFactory::new()->create([
            'product_id' => $product->id,
        ]);

        $this->assertNotEmpty($media->product_id);
        $this->assertEquals($media->product->id, $media->product_id);
        $this->assertInstanceOf(MagentoProduct::class, $media->product);
        $this->assertEquals($media->product->id, $product->id);
    }

    public function test_types_column_is_castable()
    {
        $media = MagentoProductMediaFactory::new()->create([
            'types' => [
                'image',
                'small_image',
                'thumbnail',
            ],
        ]);

        $this->assertNotEmpty($media);
        $this->assertIsArray($media->types);
        $this->assertEquals(3, count($media->types));
    }
}
