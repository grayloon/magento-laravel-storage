<?php

namespace Grayloon\MagentoStorage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagentoConfigurableProductOptionValue extends Model
{
    use HasFactory;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($optionValue) {
            $type = $optionValue->customAttributeType()->first();

            if ($type && $type->options) {
                foreach ($type->options as $option) {
                    if ($option['value'] == $optionValue->value) {
                        $optionValue->value = $option['label'];
                        break;
                    }
                }
            }

            $optionValue->value = is_array($optionValue->value)
                ? json_encode($optionValue->value)
                : $optionValue->value;
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'magento_configurable_product_option_id',
        'value',
        'synced_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'synced_at'];

    /**
     * The option value has a custom attribute type through the product option.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function customAttributeType()
    {
        return $this->hasOneThrough(MagentoCustomAttributeType::class, MagentoConfigurableProductOption::class, 'id', 'attribute_id', 'magento_configurable_product_option_id', 'attribute_type_id');
    }
}
