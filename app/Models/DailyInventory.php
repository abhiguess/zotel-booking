<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyInventory extends Model
{
    protected $table = 'daily_inventory';

    protected $fillable = [
        'room_type_id',
        'date',
        'price_1p',
        'price_2p',
        'price_3p',
        'breakfast_price_pp',
        'total_rooms',
        'booked_rooms',
        'is_blocked',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'price_1p' => 'decimal:2',
            'price_2p' => 'decimal:2',
            'price_3p' => 'decimal:2',
            'breakfast_price_pp' => 'decimal:2',
            'total_rooms' => 'integer',
            'booked_rooms' => 'integer',
            'is_blocked' => 'boolean',
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
