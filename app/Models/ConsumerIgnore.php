<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumerIgnore extends Model
{
    use HasFactory;

    protected $table = 'consumer_ignore';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];
}
