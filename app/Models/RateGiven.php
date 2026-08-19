<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RateGiven extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rate_given_table';

    protected $fillable = [
        'lead_master_id',
        'type',
        'item_id',
        'item_group_id',
        'nos',
        'rate',
        'item_gst',
        'total_taxable',
        'is_hide',
        'created_by',
    ];

    public function item()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }

    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }

    public function leadMaster()
    {
        return $this->hasOne(LeadMaster::class, 'id', 'lead_master_id');
    }
}