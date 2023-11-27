<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationFormDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'document_id',
        'key',
        'val'
    ];

    public function registration() {
        return $this->belongsTo(Registration::class);
    }

    public function document() {
        return $this->belongsTo(Document::class);
    }
}
