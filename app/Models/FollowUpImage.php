<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUpImage extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['lead_master_id', 'follow_up_id', 'image'];


    public function lead()
    {
        return $this->hasOne(LeadMaster::class, 'id', 'lead_master_id');

    }
}
