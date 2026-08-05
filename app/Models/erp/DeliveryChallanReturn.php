<?php

namespace App\Models\erp;

use App\Models\SalesMaster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryChallanReturn extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'delivery_challan_returns';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id')->select('id', 'name');
    }

    public function project()
    {
        return $this->hasOne(SalesMaster::class, 'id', 'sales_master_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select('id', 'name');
    }

    public function project_stock()
    {
        return $this->hasMany(ProjectWiseStock::class, 'delivery_challan_return_id', 'id');
    }

    public function delivery_challan_return_meta()
    {
        return $this->hasMany(DeliveryChallanReturnMeta::class, 'delivery_challan_return_id', 'id');
    }

    public function installer()
    {
        return $this->hasOne(User::class, 'id', 'installer_id');
    }
}
