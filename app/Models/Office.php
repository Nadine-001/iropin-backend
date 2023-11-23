<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Office extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'office_name',
        'office_address',
        'employment_status',
        'position',
        'office_phone',
        'SIP',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
