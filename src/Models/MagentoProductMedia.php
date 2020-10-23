<?php

namespace Grayloon\MagentoStorage\Models;

use Illuminate\Database\Eloquent\Model;

class MagentoProductMedia extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'product_id',
        'media_type',
        'label',
        'position',
        'disabled',
        'types',
        'file',
        'synced_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'synced_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'types' => 'array',
    ];

    /**
     * The Product ID belongs to the magento Product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(MagentoProduct::class, 'product_id');
    }
}
