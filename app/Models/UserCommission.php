<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class UserCommission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_commissions';

    protected $fillable = [
        'user_id',
        'effective_date',
        'commission',
        'installation',
        'sub_agent_id',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'commission' => 'decimal:2',
        'installation' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subAgent(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'sub_agent_id');
    }
}


