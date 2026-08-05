<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesQuatation extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'sales_quatations';
    protected $primarykey = 'id';
    protected $guarded = ['id'];


    public function salesQuatationMeta()
    {
        return $this->hasMany(SalesQuatationMeta::class, 'sales_quatation_id', 'id');
    }

    public function agentSalesPerson()
    {
        return $this->hasOne(AgentSalesPerson::class, 'id', 'agent_sales_person_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    public function panelCompany()
    {
        return $this->hasOne(PenalCompany::class, 'id', 'penal_company_id');
    }

    public function penalType()
    {
        return $this->hasOne(PenalType::class, 'id', 'penal_type_id')->select('id', 'name');
    }
    public function penalWatt()
    {
        return $this->hasOne(PenalWatt::class, 'id', 'penal_watt_id')->select('id', 'name');
    }
}
