<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taluka extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'talukas';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    protected $fillable = ['district_id','name'];

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }
}
