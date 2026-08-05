<?php

namespace App\Models\erp;

use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BOMMeta extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bom_metas';
    protected $primarykey = 'id';
    protected $guarded = ['id'];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'item_id');
    }
    public function itemGroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
}
