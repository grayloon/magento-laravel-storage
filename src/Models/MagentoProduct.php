<?php

namespace Grayloon\MagentoStorage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MagentoProduct extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'synced_at'];

    /**
     * The Magento Product custom attributes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function customAttributes()
    {
        return $this->morphMany(MagentoCustomAttribute::class, 'attributable');
    }

    /**
     * The Magento Product has many Ext Attributes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function extensionAttributes()
    {
        return $this->hasMany(MagentoExtensionAttribute::class);
    }

    /**
     * The categories assigned to the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function categories()
    {
        return $this->hasManyThrough(MagentoCategory::class, MagentoProductCategory::class, 'magento_product_id', 'id', 'id', 'magento_category_id');
    }

    /**
     * The Magento Product has many Product Images.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(MagentoProductMedia::class, 'product_id');
    }

    /**
     * Helper to quickly get a value from a custom attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function customAttributeValue($key)
    {
        $attribute = $this->customAttributes->where('attribute_type', $key)->first();

        return $attribute ? $attribute->value : null;
    }

    /**
     * Helper to easily obtain the main product image.
     *
     * @return null|string
     */
    public function productImage()
    {
        $attribute = $this->customAttributes->where('attribute_type', 'image')->first();

        if ($attribute && Storage::exists('public/product/'.$attribute->value)) {
            return 'product/'.$attribute->value;
        }

        return null;
    }

    /**
     * The related Magento products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function related()
    {
        return $this->hasManyThrough(MagentoProduct::class, MagentoProductLink::class, 'product_id', 'id', 'id', 'related_product_id')
            ->orderBy('position');
    }

    /**
     * A Configurable Product can have many configurable Product Type Options.
     *
     * @return void
     */
    public function configurableProductOptions()
    {
        return $this->hasMany(MagentoConfigurableProductOption::class, 'magento_product_id');
    }

    /**
     * The Configurable Product Links that belong to the Magento Configurable Product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function configurableLinks()
    {
        return $this->hasManyThrough(MagentoProduct::class, MagentoConfigurableProductLink::class, 'configurable_product_id', 'id', 'id', 'product_id');
    }

    /**
     * Determine the Sale Price is available.
     *
     * @return string|null
     */
    public function salePrice()
    {
        $salePrice = $this->customAttributes
            ->where('attribute_type', 'special_price')
            ->first();

        if (! $salePrice) {
            return;
        }

        $saleStart = $this->customAttributes
            ->where('attribute_type', 'special_from_date')
            ->first();

        $saleEnd = $this->customAttributes
            ->where('attribute_type', 'special_to_date')
            ->first();

        if (! $saleStart && ! $saleEnd) {
            return $salePrice->value;
        }

        if ($saleStart) {
            $saleStart = new Carbon($saleStart->value);
        }
        if ($saleEnd) {
            $saleEnd = new Carbon($saleEnd->value);
            $saleEnd = $saleEnd->endOfDay();
        }

        if ($saleStart) {
            if ($saleStart <= now()) {
                if (! $saleEnd) {
                    return $salePrice->value;
                }

                return ($saleEnd < now())
                    ? null
                    : $salePrice->value;
            } else { // Sale hasn't started yet.
                return;
            }
        }

        return ($saleEnd >= now())
            ? $salePrice->value
            : null;
    }

    /**
     * Resolve the price based on the tier pricing.
     *
     * @return float
     */
    public function resolvePrice()
    {
        if (! Auth::check() || ! Auth::user() instanceof MagentoCustomer || ! $this->tierPrices) {
            return $this->price;
        }

        $tierPrice = $this->tierPrices
            ->filter(fn ($tier) => Auth::user()->group_id == $tier->customer_group_id)
            ->first();

        return $tierPrice
            ? $tierPrice->value
            : $this->price;
    }

    /**
     * The Magento Product can have many tier prices.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tierPrices()
    {
        return $this->hasMany(MagentoTierPrice::class, 'magento_product_id');
    }
}
