<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesQuatationTechnicalSpecification extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'sales_quatation_id',
        'itemDescription',
        'qty',
        'size',
        'make',
        'type'
    ];
}
