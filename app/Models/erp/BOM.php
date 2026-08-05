<?php

namespace App\Models\erp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BOM extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'boms';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function meta()
    {
        return $this->hasMany(BOMMeta::class, 'boms_id', 'id');
    }
}
