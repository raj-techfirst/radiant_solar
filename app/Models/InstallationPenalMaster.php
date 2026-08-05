<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationPenalMaster extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'installation_penal_masters';
    protected $primarykey = 'id';
    protected $guarded = ['id'];
	
	public function itemgroup()
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }

}
