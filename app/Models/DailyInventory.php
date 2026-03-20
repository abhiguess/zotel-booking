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
        'total_rooms',
        'booked_rooms',
        'is_blocked',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_rooms' => 'integer',
            'booked_rooms' => 'integer',
            'is_blocked' => 'boolean',
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function getAvailableRoomsAttribute(): int
    {
        return $this->total_rooms - $this->booked_rooms;
    }

    public function hasAvailability(int $roomsNeeded = 1): bool
    {
        return $this->available_rooms >= $roomsNeeded;
    }
}
