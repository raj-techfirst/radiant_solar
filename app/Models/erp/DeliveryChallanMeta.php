<?php

namespace App\Models\erp;

use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\SerialNumberLog;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryChallanMeta extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'delivery_challan_metas';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function item()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }
    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }

    public function delivery_challan()
    {
        return $this->hasOne(DeliveryChallan::class, 'id', 'delivery_challan_id');
    }

    public function serial_numbers_count()
    {
        return $this->hasMany(SerialNumberLog::class, 'delivery_challan_meta_id', 'id');
    }
}
