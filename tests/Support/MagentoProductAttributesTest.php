<?php

use Exception;
use Illuminate\Support\Facades\Http;
use Grayloon\MagentoStorage\Tests\TestCase;
use Grayloon\MagentoStorage\Support\MagentoProductAttributes;

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
}