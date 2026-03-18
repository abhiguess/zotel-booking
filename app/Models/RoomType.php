<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'max_adults',
        'total_rooms',
        'amenities',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'max_adults' => 'integer',
            'total_rooms' => 'integer',
        ];
    }

    public function dailyInventory(): HasMany
    {
        return $this->hasMany(DailyInventory::class);
    }
}
