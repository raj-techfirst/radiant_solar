<?php

namespace App\Models\erp;

use App\Models\Product;
use App\Models\erp\Warehouse;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseStock extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'warehouse_stocks';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehous_id');
    }
    public function item()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }
    public function warehouse_stock_history()
    {
        return $this->hasMany(WarehouseStockHistory::class, 'warehous_stock_id', 'id');
    }
    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
}
