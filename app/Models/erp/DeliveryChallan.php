<?php

namespace App\Models\erp;

use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryChallan extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'delivery_challans';
    protected $primarykey = 'id';
    protected $guarded = ['id'];



    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id')->select('id', 'name');
    }

    public function warehouse_from()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_from_id')->select('id', 'name','contact_person','contact_person_no','address');
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
        return $this->hasMany(ProjectWiseStock::class, 'delivery_challan_id', 'id');
    }

    public function delivery_challan_meta()
    {
        return $this->hasMany(DeliveryChallanMeta::class, 'delivery_challan_id', 'id');
    }

    public function installer()
    {
        return $this->hasOne(User::class, 'id', 'installer_id');
    }

    public function salesQuatation()
    {
        return $this->hasOne(SalesQuatation::class, 'id', 'quotations_id');
    }
}
