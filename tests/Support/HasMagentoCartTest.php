<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoCustomerFactory;
use Grayloon\MagentoStorage\Support\HasMagentoCart;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Stringable;

class HasMagentoCartTest extends TestCase
{
    public function test_existing_cart_is_false_on_guest_without_cart()
    {
        $this->assertFalse((new FakeHasMagentoCart())->fakeExistingCart());
    }

    public function test_existing_cart_is_true_on_guest_with_cart()
    {
        $this->session(['g_cart' => 'foo']);

        $this->assertTrue((new FakeHasMagentoCart())->fakeExistingCart());
    }

    public function test_existing_cart_is_false_on_customer_without_quote_id()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());

        $this->assertFalse((new FakeHasMagentoCart())->fakeExistingCart());
    }

    public function test_existing_cart_is_true_on_customer_with_quote_id()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session(['cart_quote_id' => 'foo']);

        $this->assertTrue((new FakeHasMagentoCart())->fakeExistingCart());
    }

    public function test_shopping_cart_items_is_null_on_guest_without_cart()
    {
        $this->assertNull((new FakeHasMagentoCart())->fakeShoppingCartItems());
    }

    public function test_shopping_cart_items_is_valid_on_guest_with_cart()
    {
        $response = [
            [
                'item_id' => 1,
            ],
        ];
        Http::fake([
            '*/guest-carts/foo/items' => Http::response($response, 200),
        ]);

        $this->session(['g_cart' => 'foo']);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeShoppingCartItems());
        $this->assertEquals($response, (new FakeHasMagentoCart())->fakeShoppingCartItems());
    }

    public function test_shopping_cart_items_is_null_on_customer_without_quote_id()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());

        $this->assertNull((new FakeHasMagentoCart())->fakeShoppingCartItems());
    }

    public function test_shopping_cart_items_is_valid_on_customer_with_quote_id()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session(['cart_quote_id' => 'foo']);
        config(['magento.store_code' => 'foo']);

        $response = [
            [
                'item_id' => 1,
            ],
        ];
        Http::fake([
            '*/carts/mine/items' => Http::response($response, 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeShoppingCartItems());
        $this->assertEquals($response, (new FakeHasMagentoCart())->fakeShoppingCartItems());
    }

    public function test_create_cart_can_create_guest_cart()
    {
        Http::fake([
            '*/guest-carts' => Http::response('FAKE_TOKEN', 200),
        ]);

        (new FakeHasMagentoCart())->fakeCreateCart();

        $this->assertEquals('FAKE_TOKEN', session('g_cart'));
    }

    public function test_create_cart_can_get_quote_id_of_customer_cart()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session(['customer_api_token' => 'FAKE_TOKEN']);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine' => Http::response(1, 200),
        ]);

        (new FakeHasMagentoCart())->fakeCreateCart();

        $this->assertEquals(1, session('cart_quote_id'));
        $this->assertNotNull(session('customer_api_token'));
    }

    public function test_add_item_to_cart_can_add_item_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/items' => Http::response([
                'sku' => 'foo',
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeAddItemToCart('foo', 1));
        $this->assertEquals(1, session('ttl_qty_count'));
        $this->assertEquals(['sku' => 'foo'], (new FakeHasMagentoCart())->fakeAddItemToCart('foo', 1));
        $this->assertEquals(2, session('ttl_qty_count'));
    }

    public function test_add_item_to_cart_returns_null_when_qty_not_available()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/items' => Http::response([
                'message' => 'The requested qty is not available',
            ], 200),
        ]);

        $this->assertNull((new FakeHasMagentoCart())->fakeAddItemToCart('foo', 1));
    }

    public function test_add_item_to_cart_can_add_item_as_signed_in_customer()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session(['customer_api_token' => 'FAKE_TOKEN']);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine/items' => Http::response([
                'sku' => 'foo',
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeAddItemToCart('foo', 1));
        $this->assertEquals(1, session('ttl_qty_count'));
        $this->assertEquals(['sku' => 'foo'], (new FakeHasMagentoCart())->fakeAddItemToCart('foo', 1));
        $this->assertEquals(2, session('ttl_qty_count'));
        $this->assertNotNull(session('customer_api_token'));
    }

    public function test_can_get_cart_total_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/totals' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeCartTotals());
    }

    public function test_can_get_cart_total_as_signed_in_customer()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session([
            'customer_api_token' => 'FAKE_TOKEN',
            'cart_quote_id' => 'FAKE_QUOTE_ID',
        ]);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine/totals' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeCartTotals());
    }

    public function test_cart_total_returns_null_as_guest_without_cart()
    {
        Http::fake([
            '*/guest-carts/FAKE_CART/totals' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertNull((new FakeHasMagentoCart())->fakeCartTotals());
    }

    public function test_create_cart_sanitizes_api_token_with_quotes_in_response()
    {
        Http::fake([
            '*/guest-carts' => Http::response('"FAKE_TOKEN_IN_QUOTES"', 200),
        ]);

        (new FakeHasMagentoCart())->fakeCreateCart();

        $this->assertNotInstanceOf(Stringable::class, session('g_cart'));
        $this->assertEquals('FAKE_TOKEN_IN_QUOTES', session('g_cart'));
    }

    public function test_estimate_shipping_method_can_estimate_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/estimate-shipping-methods' => Http::response([
                0 => [
                    'amount' => 4,
                ],
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeEstimateShippingMethod());
    }

    public function test_estimate_shipping_method_can_estimate_as_customer()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session([
            'customer_api_token' => 'FAKE_TOKEN',
            'cart_quote_id' => 'FAKE_QUOTE_ID',
        ]);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine/estimate-shipping-methods' => Http::response([
                0 => [
                    'amount' => 4,
                ],
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeEstimateShippingMethod());
    }

    public function test_estimate_shipping_method_can_returns_empty_on_guest_without_cart()
    {
        $this->assertNull((new FakeHasMagentoCart())->fakeEstimateShippingMethod());
    }

    public function test_estimate_shipping_method_can_pass_address_attributes_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/estimate-shipping-methods' => Http::response([
                0 => [
                    'amount' => 4,
                ],
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeEstimateShippingMethod([
            'region_id' => 1,
            'region' => 'Indiana',
            'country_id' => 'US',
            'postcode' => '*',
        ]));
    }

    public function test_can_update_totals_information_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/totals-information' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeUpdateTotalsInformation([]));
    }

    public function test_can_update_totals_information_as_customer()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session([
            'customer_api_token' => 'FAKE_TOKEN',
            'cart_quote_id' => 'FAKE_QUOTE_ID',
        ]);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine/totals-information' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeUpdateTotalsInformation([]));
    }

    public function test_update_totals_information_can_returns_empty_on_guest_without_cart()
    {
        $this->assertNull((new FakeHasMagentoCart())->fakeUpdateTotalsInformation());
    }

    public function test_can_update_shipping_information_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/shipping-information' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeUpdateShippingInformation([]));
    }

    public function test_can_update_shipping_information_as_customer()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session([
            'customer_api_token' => 'FAKE_TOKEN',
            'cart_quote_id' => 'FAKE_QUOTE_ID',
        ]);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine/shipping-information' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakeUpdateShippingInformation([]));
    }

    public function test_update_shipping_information_can_returns_empty_on_guest_without_cart()
    {
        $this->assertNull((new FakeHasMagentoCart())->fakeUpdateShippingInformation());
    }

    public function test_can_payment_methods_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/payment-methods' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakePaymentMethods());
    }

    public function test_can_payment_methods_as_customer()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session([
            'customer_api_token' => 'FAKE_TOKEN',
            'cart_quote_id' => 'FAKE_QUOTE_ID',
        ]);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine/payment-methods' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertIsArray((new FakeHasMagentoCart())->fakePaymentMethods());
    }

    public function test_payment_methods_can_returns_empty_on_guest_without_cart()
    {
        $this->assertNull((new FakeHasMagentoCart())->fakePaymentMethods());
    }

    public function test_can_submit_payment_as_customer()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());
        $this->session([
            'customer_api_token' => 'FAKE_TOKEN',
            'cart_quote_id' => 'FAKE_QUOTE_ID',
        ]);
        config(['magento.store_code' => 'foo']);

        Http::fake([
            '*/carts/mine/payment-information' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertInstanceOf(Response::class, (new FakeHasMagentoCart())->fakeSubmitPayment(['foo']));
    }

    public function test_submit_payment__returns_empty_on_guest_without_cart()
    {
        $this->assertNull((new FakeHasMagentoCart())->fakeSubmitPayment([]));
    }

    public function test_can_submit_payments_as_guest()
    {
        $this->session(['g_cart' => 'FAKE_CART']);

        Http::fake([
            '*/guest-carts/FAKE_CART/payment-information' => Http::response([
                'id' => 1,
            ], 200),
        ]);

        $this->assertInstanceOf(Response::class, (new FakeHasMagentoCart())->fakeSubmitPayment([]));
    }
}

class FakeHasMagentoCart
{
    use HasMagentoCart;

    public function fakeExistingCart()
    {
        return $this->existingCart();
    }

    public function fakeShoppingCartItems()
    {
        return $this->shoppingCartItems();
    }

    public function fakeCreateCart()
    {
        return $this->createCart();
    }

    public function fakeAddItemToCart($sku, $qty)
    {
        return $this->addItemToCart($sku, $qty);
    }

    public function fakeCartTotals()
    {
        return $this->cartTotals();
    }

    public function fakeEstimateShippingMethod($addressAttributes = [])
    {
        return $this->estimateShippingMethod($addressAttributes = []);
    }

    public function fakeUpdateTotalsInformation($attributes = [])
    {
        return $this->updateTotalsInformation($attributes = []);
    }

    public function fakeUpdateShippingInformation($attributes = [])
    {
        return $this->updateShippingInformation($attributes = []);
    }

    public function fakePaymentMethods()
    {
        return $this->paymentMethods();
    }

    public function fakeSubmitPayment($attributes = [])
    {
        return $this->submitPayment($attributes = []);
    }
}
