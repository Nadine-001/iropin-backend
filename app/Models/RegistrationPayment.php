<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationPayment extends Model
{
    use HasFactory;

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
