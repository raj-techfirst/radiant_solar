<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estimate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estimates';
    protected $fillable = [
        'lead_master_id',
        'user_id',
        'company_profile_id',
        'manager_id',
        'assign_id',
        'estimate_title',
        'estimate_date',
        'expiry_date',
        'remark',
        'subtotal',
        'discount',
        'total',
    ];

    public function leadMaster()
    {
        return $this->hasOne(LeadMaster::class, 'id', 'lead_master_id');
    }

    public function estimateItem()
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id', 'id');
    }

    public function company()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'company_profile_id');
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function assign()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'assign_id');
    }
}
