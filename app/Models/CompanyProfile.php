<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_profiles';
    protected $fillable = ['user_id', 'parent_id', 'manager_id', 'user_type', 'state_id', 'city_id', 'indiamart_key', 'justdial_key', 'is_indiamart', 'is_justdial', 'business_name', 'address','terms_conditions'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function owner()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'parent_id');
    }

    public function manager()
    {
        return $this->hasOne(CompanyProfile::class, 'id', 'manager_id');
    }

    public function state()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }
    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }

    public function estimate()
    {
        return $this->hasOne(Estimate::class, 'company_profile_id', 'id');
    }
}
