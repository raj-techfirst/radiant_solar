<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvaterImage extends Model
{
    use HasFactory;
    protected $table = 'invater_images';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function installation()
    {
        return $this->hasMany(Installation::class, 'id', 'installation_id');
    }
}
