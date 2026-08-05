<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'products';
    //protected $fillable = ['user_id', 'category_id', 'company_profile_id', 'product_name', 'product_price', 'description'];
    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
}
