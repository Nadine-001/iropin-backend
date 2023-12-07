<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webinar extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'date',
        'speaker',
        'price',
        'place',
        'description',
        'poster',
        'materi',
        'link',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
