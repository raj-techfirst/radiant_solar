<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RateCalculatorMeta extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'rate_calculator_metas';
    protected $primarykey = 'id';
    protected $guarded = ['id'];
}
