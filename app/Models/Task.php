<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tasks';
    protected $fillable = ['id', 'user_id', 'company_profile_id', 'manager_id', 'assign_id', 'product_id', 'task_name', 'description', 'timespand', 'hours', 'minutes', 'task_date', 'expiry_date', 'priority', 'status'];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public function company()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'assign_id');
    }
}
