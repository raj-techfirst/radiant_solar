<?php

namespace App\Models\erp;

use App\Models\ItemGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerialNumber extends Model
{
    use HasFactory, SoftDeletes;
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'location_id')->select('name');
    }

    public function purchase()
    {
        return $this->hasOne(PurchaseDirect::class, 'id', 'purchase_direct_id');
    }

}
