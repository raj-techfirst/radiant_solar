<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalData extends Model
{
    use HasFactory;

    protected $table = 'portal_data';

    protected $guarded = ['id'];

    protected $casts = [
        'sales_master_id' => 'integer',
    ];
}
