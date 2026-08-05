<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'categories';
    // protected $fillable = ['user_id','company_profile_id','category_name'];
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function product()
    {
        return $this->hasOne(Product::class, 'category_id', 'id');
    }
}
