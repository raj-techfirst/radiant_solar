<?php

namespace App\Models;

use App\Models\erp\DeliveryChallan;
use App\Models\erp\SerialNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialNumberLog extends Model
{
    use HasFactory;
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function serialNumbers()
    {
        return $this->hasOne(SerialNumber::class, 'id', 'serial_number_id');
    }
    public function delivery_challan()
    {
        return $this->hasOne(DeliveryChallan::class, 'id', 'delivery_challan_id');
    }

}
