<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenalWatt extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'penal_watts';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function penalCompany()
    {
        return $this->hasOne(PenalCompany::class, 'id', 'penal_company_id');
    }
}
