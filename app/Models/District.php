<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'districts';
    protected $primarykey = 'id';
    protected $guarded = ['id'];
    // protected $fillable = ['id', 'state_id', 'name'];
	
	
    public function state()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }
}
