<?php

namespace App\Models\erp;

use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDirectMeta extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'purchase_direct_metas';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function item()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }

    public function purchase_direct()
    {
        return $this->hasOne(PurchaseDirect::class, 'id', 'purchase_direct_id');
    }

    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
    public function serial_numbers_count()
    {
        return $this->hasMany(SerialNumber::class, 'purchase_direct_meta_id', 'id');
    }
}
