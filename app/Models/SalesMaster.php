<?php

namespace App\Models;

use App\Models\erp\BOM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesMaster extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'sales_masters';
    protected $fillable = ['id','user_id','consumer_number','master_create_date','consumer_type','consumer_name','gst_number','district','taluka','city','address','pin_code','contact_number','email','aadhaar_number','bank_name','bank_account','ifsc_code','ifsc_code','contracted_load','phase','division','sub_division_id','circle','discom','reference','remark','commission_amount','sub_commission_amount','installation_amount'];

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district_id')->select('id','name','state_id');
    }

    public function taluka()
    {
        return $this->hasOne(Taluka::class, 'id', 'taluka_id')->select('id','name');
    }

    public function village()
    {
        return $this->hasOne(Village::class, 'id', 'village_id');
    }

    public function document()
    {
        return $this->hasMany(Document::class, 'sales_master_id', 'id');
    }

    public function subDivision()
    {
        return $this->hasMany(SubDivision::class, 'id', 'sub_division_id')->select('id','name','division_name','circle_name','discom');
    }
    public function subDivisionPDF()
    {
        return $this->hasOne(SubDivision::class, 'id', 'sub_division_id');
    }

    public function installation()
    {
        return $this->hasOne(Installation::class, 'sales_master_id', 'id');
    }
    public function paymetCollection(){
        return $this->hasMany(PaymetCollection::class, 'sales_master_id', 'id');
    }

    public function agentsalesperson()
    {
        return $this->hasOne(AgentSalesPerson::class, 'id', 'agent_sales_person_id')->select('id','name','number');
    }

    public function salesquatation()
    {
        return $this->hasOne(SalesQuatation::class, 'id', 'sales_quatation_id')->select('id', 'form_type', 'nos',  'penal_company_id', 'penal_type_id', 'penal_watt_id', 'penal_nos', 'pv_capacity_kw', 'inveter_company_id', 'inveter_capacity', 'no_of_inveter', 'structure','quatation_type');
    }
    public function salesquatationfull()
    {
        return $this->hasOne(SalesQuatation::class, 'id', 'sales_quatation_id');
    }

    public function lead()
    {
        return $this->hasOne(LeadMaster::class, 'id', 'lead_master_id');
    }
    public function panel()
    {
        return $this->hasOne(PenalCompany::class, 'id', 'penal_company_id')->select('id','name');
    }


    public function panelwatt()
    {
        return $this->hasOne(PenalWatt::class, 'id', 'penal_watt_id')->select('id','name');
    }
    public function inveter()
    {
        return $this->hasOne(InveterCompany::class, 'id', 'inveter_company_id')->select('id','name');
    }
    public function bom()
    {
        return $this->hasOne(BOM::class, 'id', 'bom_id')->select('id','bom_name');
    }
     public function allpanels()
    {
        return $this->hasMany(InstallationPenalMaster::class, 'sales_master_id', 'id');
    }
}

