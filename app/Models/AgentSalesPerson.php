<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentSalesPerson extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'agent_sales_people';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function salesQuatation()
    {
        return $this->hasOne(SalesQuatation::class, 'agent_sales_person_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
