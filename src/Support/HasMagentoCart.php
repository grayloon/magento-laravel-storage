<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\Magento\Magento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait HasMagentoCart
{
    use HasMagentoAuthentication;

    /**
     * Determine if authenticated customer or guest has an open cart.
     *
     * @return bool
     */
    protected function existingCart()
    {
        return ($this->customerIsSignedIn())
            ? session()->has('cart_quote_id')
            : session()->has('g_cart');
    }

    /**
     * Get the shopping cart items of the user.
     *
     * @return null|array
     */
    protected function shoppingCartItems()
    {
        if (! $this->existingCart()) {
            return;
        }

        return ($this->customerIsSignedIn())
            ? $this->getCustomerCart()
            : $this->getGuestCart();
    }

    /**
     * Counts the number of products and quantity currently in the cart.
     *
     * @return int
     */
    protected function cartCount()
    {
        if (! $this->existingCart()) {
            return 0;
        }

        if ($this->customerIsSignedIn()) {
            $magento = new Magento();
            $magento->token = session('customer_api_token');

            return $magento->api('carts')->mine()->json()['items_qty'] ??= 0;
        } else {
            return (new Magento())->api('guestCarts')->cart(session('g_cart'))->json()['items_qty'] ??= 0;
        }
    }

    /**
     * Auth customers always have carts.
     * Create guest cart and assign it to the user session.
     *
     * @return void
     */
    protected function createCart()
    {
        $this->customerIsSignedIn()
            ? session(['cart_quote_id' => $this->magentoCustomerToken()->api('carts')->create()->json()])
            : session(['g_cart' => $this->stripStringQuotes((new Magento())->api('guestCarts')->create()->body())]);

        return $this;
    }

    /**
     * Sometimes Magento will include quotes in their strings.
     * If this is the case, we need to remove them to prevent double quoting strings.
     *
     * @param  string  $text
     * @return string
     */
    protected function stripStringQuotes($text)
    {
        return Str::of($text)->replace('"', '')->__toString();
    }

    /**
     * View the Guest Cart Total along with the items that are in the cart.
     *
     * @return array
     */
    protected function getGuestCart()
    {
        return (new Magento())->api('guestCarts')->items(session('g_cart'))->json();
    }

    /**
     * View the authenticated customer cart items along with the items that are in the cart.
     *
     * @return void
     */
    protected function getCustomerCart()
    {
        return $this->magentoCustomerToken()->api('cartItems')->mine()->json();
    }

    /**
     * Add a specified item to the cart.
     *
     * @return string|array
     */
    protected function addItemToCart($sku, $quantity)
    {
        $response = $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('cartItems')->addItem(session('cart_quote_id'), $sku, $quantity)->json()
            : (new Magento())->api('guestCarts')->addItem(session('g_cart'), $sku, $quantity)->json();

        if (isset($response['message']) && $response['message'] === 'The requested qty is not available') {
            return;
        }

        return $response;
    }

    /**
     * Edit a specified item in the cart.
     *
     * @return string|array
     */
    protected function editItem($itemId, $body = [])
    {
        $response = $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('cartItems')->editItem($itemId, $body)->json()
            : (new Magento())->api('guestCarts')->editItem(session('g_cart'), $itemId, $body)->json();

        if (isset($response['message']) && $response['message'] === 'The requested qty is not available') {
            return;
        }

        return $response;
    }

    /**
     * Remove a specified item to the cart.
     *
     * @return string|array
     */
    protected function removeItemFromCart($itemId)
    {
        $response = $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('cartItems')->removeItem($itemId)->json()
            : (new Magento())->api('guestCarts')->removeItem(session('g_cart'), $itemId)->json();

        return $response;
    }

    /**
     * Get the Cart Totals for the specified customer or guest cart.
     *
     * @return array
     */
    protected function cartTotals()
    {
        if (! $this->existingCart()) {
            return;
        }

        return $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('cartTotals')->mine()->json()
            : (new Magento())->api('guestCarts')->totals(session('g_cart'))->json();
    }

    protected function estimateShippingMethod($addressAttributes = [])
    {
        if (! $this->existingCart()) {
            return;
        }

        return $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('carts')->estimateShippingMethods(['address' => $addressAttributes])->json()
            : (new Magento())->api('guestCarts')->estimateShippingMethods(session('g_cart'), ['address' => $addressAttributes])->json();
    }

    protected function updateTotalsInformation($attributes = [])
    {
        if (! $this->existingCart()) {
            return;
        }

        return $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('carts')->totalsInformation($attributes)->json()
            : (new Magento())->api('guestCarts')->totalsInformation(session('g_cart'), $attributes)->json();
    }

    protected function updateShippingInformation($attributes = [])
    {
        if (! $this->existingCart()) {
            return;
        }

        return $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('carts')->shippingInformation($attributes)->json()
            : (new Magento())->api('guestCarts')->shippingInformation(session('g_cart'), $attributes)->json();
    }

    protected function paymentMethods()
    {
        if (! $this->existingCart()) {
            return;
        }

        return $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('carts')->paymentMethods()->json()
            : (new Magento())->api('guestCarts')->paymentMethods(session('g_cart'))->json();
    }

    protected function submitPayment($attributes = [])
    {
        if (! $this->existingCart()) {
            return;
        }

        return $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('carts')->paymentInformation($attributes)
            : (new Magento())->api('guestCarts')->paymentInformation(session('g_cart'), $attributes);
    }

    /**
     * Apply a coupon code to the cart.
     *
     * @param  string $couponCode
     * @return array
     */
    protected function applyCouponCode($couponCode)
    {
        if (! $this->existingCart()) {
            return;
        }

        return $this->customerIsSignedIn()
            ? $this->magentoCustomerToken()->api('carts')->couponCode($couponCode)
            : (new Magento())->api('guestCarts')->couponCode(session('g_cart'), $couponCode);
    }

    private function magentoCustomerToken()
    {
        $magento = new Magento();
        $magento->token = session('customer_api_token');

        return $magento;
    }
}
