<?php

namespace App\Models\erp;

use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectWiseStock extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'project_wise_stocks';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function delivery_challan()
    {
        return $this->hasOne(DeliveryChallan::class, 'id', 'delivery_challan_id');
    }

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

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function project()
    {
        return $this->hasOne(SalesMaster::class, 'id', 'sales_master_id');
    }

    public function project_wise_history()
    {
        return $this->hasMany(ProjectWiseStockHistory::class, 'project_wise_stock_id', 'id');
    }

    public function installer()
    {
        return $this->hasOne(User::class, 'id', 'installer_id');
    }
}
