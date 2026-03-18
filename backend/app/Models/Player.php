<?php

namespace App\Models;

use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'nickname',
        'country_id',
        'name',
        'surname',
        'date_of_birth',
        'primary_position',
        'secondary_position',
        'photo_url',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
