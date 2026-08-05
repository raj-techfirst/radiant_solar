<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadTransection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lead_transections';
    protected $fillable = ['lead_master_id', 'assign_id', 'assign_by'];
}
