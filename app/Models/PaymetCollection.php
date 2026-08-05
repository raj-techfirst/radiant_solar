<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymetCollection extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'paymet_collections';
    protected $primarykey = 'id';
    protected $guarded = ['id'];



    public function salesMaster()
    {
        return $this->hasOne(SalesMaster::class, 'id', 'sales_master_id')->select('id', 'consumer_number', 'consumer_type', 'consumer_name', 'agent_sales_person_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function editby()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected $appends = [
        'file_path',
        'credited_bank_name'
    ];

    public function getFilePathAttribute()
    {
        return $this->file
            ? asset('uploads/payment_collections/' . $this->file)
            : null;
    }
    public function getCreditedBankNameAttribute()
    {
        return $this->bank ? $this->bank->name : null;
    }

}
