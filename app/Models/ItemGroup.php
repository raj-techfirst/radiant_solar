<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'item_groups';
    protected $primarykey = 'id';
    protected $guarded = ['id'];


    public function panel_company()
    {
        return $this->hasOne(PenalCompany::class, 'id', 'panel_company_id');
    }

    public function panel_type()
    {
        return $this->hasOne(PenalType::class, 'id', 'panel_type_id');
    }

    public function panel_watt()
    {
        return $this->hasOne(PenalWatt::class, 'id', 'panel_watt_id');
    }

    public function inveter_company()
    {
        return $this->hasOne(InveterCompany::class, 'id', 'inveter_company_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
}
