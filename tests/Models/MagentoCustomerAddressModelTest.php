<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoCustomerAddress;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomerAddressFactory;

class MagentoCustomerAddressModelTest extends TestCase
{
    public function test_can_create_magento_customer_address()
    {
        $address = MagentoCustomerAddressFactory::new()->create();

        $this->assertNotEmpty($address);
    }

    public function test_can_get_custom_attributes_on_magento_customer_address()
    {
        $address = MagentoCustomerAddressFactory::new()->create();

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type'   => MagentoCustomerAddress::class,
            'attributable_id'     => $address->id,
        ]);

        $attributes = $address->customAttributes()->get();

        $this->assertNotEmpty($address, $attributes);
        $this->assertEquals(1, $attributes->count());
        $this->assertEquals(MagentoCustomerAddress::class, $attributes->first()->attributable_type);
    }

    public function test_can_update_instead_of_creating_row_custom_attributes_on_customer()
    {
        $address = MagentoCustomerAddressFactory::new()->create();

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type'   => MagentoCustomerAddress::class,
            'attributable_id'     => $address->id,
            'attribute_type'      => 'foo',
            'value'               => 'bar',
        ]);

        $attribute = $address->customAttributes()->updateOrCreate(['attribute_type' => 'foo'], [
            'value'=> 'baz',
        ]);

        $this->assertEquals(1, $address->customAttributes()->count());
        $this->assertEquals('baz', $attribute->value);
    }
}
