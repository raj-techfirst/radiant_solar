<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationInvater extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'installation_invaters';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function salesMaster()
    {
        return $this->hasMany(SalesMaster::class, 'id', 'sales_master_id');
    }

    public function company()
    {
        return $this->hasOne(InveterCompany::class, 'id', 'invater_id')->select('id', 'name');
    }

    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id')->select('id', 'inverter_type');
    }
}
