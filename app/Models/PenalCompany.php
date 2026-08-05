<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenalCompany extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'penal_companies';
    protected $primarykey = 'id';
    protected $guarded = ['id'];
}
