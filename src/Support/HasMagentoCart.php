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
     * Auth customers always have carts.
     * Create guest cart and assign it to the user session.
     *
     * @return void
     */
    protected function createCart()
    {
        $this->customerIsSignedIn()
            ? session(['cart_quote_id' => $this->magentoCustomerToken()->api('carts')->mine()->json()['id']])
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

        (session()->has('ttl_qty_count'))
            ? session(['ttl_qty_count' => session('ttl_qty_count') + $quantity])
            : session(['ttl_qty_count' => $quantity]);

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

    private function magentoCustomerToken()
    {
        $magento = new Magento();
        $magento->token = session('customer_api_token');

        return $magento;
    }
}
