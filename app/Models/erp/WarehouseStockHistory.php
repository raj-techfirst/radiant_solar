<?php

namespace App\Models\erp;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseStockHistory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'warehouse_stock_histories';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function item()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }

    public function purchase_direct_meta()
    {
        return $this->hasOne(PurchaseDirectMeta::class, 'id', 'purchase_direct_meta_id')->withTrashed();
    }

    public function delivery_challan_meta()
    {
        return $this->hasOne(DeliveryChallanMeta::class, 'id', 'delivery_challan_meta_id')->withTrashed();
    }
}
