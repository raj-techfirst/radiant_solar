<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusMaster extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'status_masters';
    protected $fillable = ['user_id','company_profile_id','status_name'];
}
