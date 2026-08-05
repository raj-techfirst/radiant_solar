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

class ProjectWiseStockAdjust extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'project_wise_stock_adjusts';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function project()
    {
        return $this->hasOne(SalesMaster::class, 'id', 'sales_master_id');
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
    public function installer()
    {
        return $this->hasOne(User::class, 'id', 'installer_id');
    }

    
}
