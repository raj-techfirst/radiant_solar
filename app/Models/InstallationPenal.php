<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationPenal extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'installation_penals';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function salesMaster()
    {
        return $this->hasMany(SalesMaster::class, 'id', 'sales_master_id');
    }

    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id')->select('id', 'p_type');
    }
}
