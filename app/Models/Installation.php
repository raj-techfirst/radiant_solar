<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installation extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'installations';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function salesMaster()
    {
        return $this->hasMany(SalesMaster::class, 'id', 'sales_master_id');
    }
    public function panelwatt()
    {
        return $this->hasOne(PenalWatt::class, 'id', 'penal_watt_id')->select('id', 'name');
    }
    public function panelcompany()
    {
        return $this->hasOne(PenalCompany::class, 'id', 'penal_company_id')->select('id', 'name');
    }
    public function paneltype()
    {
        return $this->hasOne(PenalType::class, 'id', 'penal_type_id')->select('id', 'name');
    }

    public function penalImage()
    {
        return $this->hasMany(PenalImage::class, 'installation_id', 'id')->select('id', 'installation_id', 'image');
    }

    public function invaterImages()
    {
        return $this->hasMany(InvaterImage::class, 'installation_id', 'id')->select('id', 'installation_id', 'image');
    }
    public function installationPenals()
    {
        return $this->hasMany(InstallationPenal::class, 'installation_id', 'id')->select('id', 'installation_id', 'serial_no','item_group_id');
    }
    public function invater()
    {
        return $this->hasMany(InstallationInvater::class, 'installation_id', 'id')->select('id', 'installation_id', 'invater_id', 'invater_kw', 'serial_no_of_inverter', 'voltage', 'model_number', 'item_group_id');
    }

    public function installationItems()
    {
        return $this->hasMany(InstallationItems::class, 'installation_id', 'id')->with('product');
    }
}
