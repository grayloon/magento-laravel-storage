<?php

namespace Grayloon\MagentoStorage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagentoConfigurableProductOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'attribute_id',
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
     * The attribute attribute_id belongs to the Magento Product Attribute resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(MagentoProductAttribute::class, 'attribute_id');
    }
}
