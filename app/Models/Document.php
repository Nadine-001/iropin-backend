<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'ext',
        'file_name',
        'is_checked'
    ];

    function registration_detail() {
        return $this->hasOne(RegistrationFormDetail::class);
    }
}
