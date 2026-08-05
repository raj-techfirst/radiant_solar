<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loandisbursement extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'loandisbursements';
    protected $primarykey = 'id';
    protected $guarded = ['id'];
}
