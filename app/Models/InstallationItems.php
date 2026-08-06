<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'installation_items';
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id', 'id');
    }
}
