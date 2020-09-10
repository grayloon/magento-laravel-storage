<?php

namespace Grayloon\MagentoStorage\Tests;

use Illuminate\Support\Facades\Auth;
use Grayloon\MagentoStorage\Models\MagentoCustomer;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoCustomerAddress;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomerFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomerAddressFactory;

class MagentoCustomerModelTest extends TestCase
{
    public function test_can_create_magento_customer()
    {
        $customer = MagentoCustomerFactory::new()->create();

        $this->assertNotEmpty($customer);
    }

    public function test_can_get_custom_attributes_on_magento_customer()
    {
        $customer = MagentoCustomerFactory::new()->create();

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type'   => MagentoCustomer::class,
            'attributable_id'     => $customer->id,
        ]);

        $attributes = $customer->customAttributes()->get();

        $this->assertNotEmpty($customer, $attributes);
        $this->assertEquals(1, $attributes->count());
        $this->assertEquals(MagentoCustomer::class, $attributes->first()->attributable_type);
    }

    public function test_can_update_instead_of_creating_row_custom_attributes_on_customer()
    {
        $customer = MagentoCustomerFactory::new()->create();

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type'   => MagentoCustomer::class,
            'attributable_id'     => $customer->id,
            'attribute_type'      => 'foo',
            'value'               => 'bar',
        ]);

        $attribute = $customer->customAttributes()->updateOrCreate(['attribute_type' => 'foo'], [
            'value'=> 'baz',
        ]);

        $this->assertEquals(1, $customer->customAttributes()->count());
        $this->assertEquals('baz', $attribute->value);
    }

    public function test_magento_customer_has_many_addresses()
    {
        $customer = MagentoCustomerFactory::new()->create();

        MagentoCustomerAddressFactory::new()->count(5)->create([
            'customer_id' => $customer->id,
        ]);

        $this->assertEquals(5, $customer->addresses()->count());
        $this->assertInstanceOf(MagentoCustomerAddress::class, $customer->addresses()->first());
    }

    public function test_magento_customer_is_authenticatable()
    {
        $customer = MagentoCustomerFactory::new()->create();

        $this->actingAs($customer);

        $this->assertAuthenticated();
        $this->assertInstanceOf(MagentoCustomer::class, Auth::user());
    }
}
