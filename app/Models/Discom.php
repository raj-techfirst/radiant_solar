<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discom extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'discoms';
    protected $primarykey = 'id';
    protected $guarded = ['id'];
}
