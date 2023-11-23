<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Biodata extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'prefix',
        'sufix',
        'NIK',
        'birthplace',
        'birthdate',
        'gender',
        'religion',
        'mobile_phone',
        'whatsapp_number',
        'foto_KTP',
        'pas_foto',
        'STR_number',
        'publish_date',
        'exp_date',
        'STR_file',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

