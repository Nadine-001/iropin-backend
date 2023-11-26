<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class File extends Model
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

    public function licence_form_detail(): HasOne
    {
        return $this->belongsTo(LicenceFormDetail::class);
    }
}
