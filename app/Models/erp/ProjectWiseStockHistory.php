<?php

namespace App\Models\erp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectWiseStockHistory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'project_wise_stock_histories';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function delivery_challan_meta()
    {
        return $this->hasOne(DeliveryChallanMeta::class, 'id', 'delivery_challan_meta_id');
    }
    
}
