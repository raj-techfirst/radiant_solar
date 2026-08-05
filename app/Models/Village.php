<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Village extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'villages';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    public function taluka()
    {
        return $this->hasOne(Taluka::class, 'id', 'taluka_id');
    }
}
