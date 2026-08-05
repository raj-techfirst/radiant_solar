<?php

namespace App\Models\erp;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDirect extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'purchase_directs';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    
    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehous_id');
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }

    public function purchase_direct_meta()
    {
        return $this->hasMany(PurchaseDirectMeta::class, 'purchase_direct_id', 'id');
    }
}
