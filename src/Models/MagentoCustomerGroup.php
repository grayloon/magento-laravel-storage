<?php

namespace Grayloon\MagentoStorage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagentoCustomerGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'code',
        'tax_class_id',
        'tax_class_name',
    ];
}
