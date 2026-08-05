<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesQuatationMeta extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'sales_quatation_id',
        'type',
        'item_id',
        'item_group_id',
        'nos',
        'rate',
        'item_gst',
    ];

    public function item()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }

    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }

    public function salesQuatation()
    {
        return $this->hasMany(SalesQuatation::class, 'id', 'sales_quatation_id');
    }
}
