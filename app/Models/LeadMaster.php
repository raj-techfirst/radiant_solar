<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadMaster extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'lead_masters';
    protected $fillable = [
        'user_id',
        'manager_id',
        'company_profile_id',
        'assign_id',
        'name',
        'last_name',
        'mobile',
        'email',
        'last_contacted',
        'reminder_date',
        'address',
        'kw',
        'reference',
        'agent_sales_person_id',
        'lead_status_id',
        'remark',
        'branch_id',
        'source_id'
    ];



    public function company()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'assign_id');
    }

    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'company_profile_id');
    }

    public function source()
    {
        return $this->hasOne(LeadSource::class, 'id', 'source_id');
    }


    public function agentSalesPerson()
    {
        return $this->hasOne(AgentSalesPerson::class, 'id', 'agent_sales_person_id');
    }

    public function followUp()
    {
        return $this->hasMany(FollowUp::class, 'lead_master_id', 'id');
    }

    public function followUpImage()
    {
        return $this->hasMany(FollowUpImage::class, 'lead_master_id', 'id');
    }

    public function leadStatus()
    {
        return $this->hasOne(LeadStatus::class, 'id', 'lead_status_id');
    }
}
