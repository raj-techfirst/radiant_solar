<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationImage extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'installation_images';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function installation()
    {
        return $this->hasMany(Installation::class, 'id', 'installation_id');
    }
}
