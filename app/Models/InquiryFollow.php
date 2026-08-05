<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryFollow extends Model
{
    use HasFactory;

    public function assignPerson()
    {
        return $this->hasOne(AgentSalesPerson::class, 'id', 'assign_person_id');
    }

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id', 'id');
    }
}
