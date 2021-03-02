<?php

namespace Grayloon\MagentoStorage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagentoConfigurableProductOption extends Model
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
        
        static::deleting(fn ($option) => $option->optionValues()->delete());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'attribute_type_id',
        'magento_product_id',
        'label',
        'position',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'synced_at'];

    /**
     * The attribute magento_product_id belongs to the Magento Product resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(MagentoProduct::class, 'magento_product_id');
    }

    /**
     * The Attribute Type belongs to the Magento Custom Attribute resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(MagentoCustomAttributeType::class, 'attribute_type_id', 'attribute_id');
    }

    /**
     * A Configurable Product Option can have many configurable values.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function optionValues()
    {
        return $this->hasMany(MagentoConfigurableProductOptionValue::class, 'magento_configurable_product_option_id', 'id');
    }
}
