<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'follow_ups';
    protected $fillable = ['lead_master_id', 'call_detail', 'call_recording', 'remark', 'reminder_date', 'follow_up_by', 'status_id', 'rate_data'];

    protected $casts = [
        'rate_data' => 'array',
    ];

    public function company()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'follow_up_by');
    }
    public function image()
    {
        return $this->hasMany(FollowUpImage::class, 'follow_up_id', 'id');
    }

    public function lead()
    {
        return $this->hasOne(LeadMaster::class, 'id', 'lead_master_id');

    }
    public function status()
    {
        return $this->hasOne(LeadStatus::class, 'id', 'status_id');

    }
}
