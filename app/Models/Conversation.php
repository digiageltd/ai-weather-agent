<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'user_id', 'messages'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function casts(): array
    {
        return [
            'messages' => 'array',
        ];
    }
}
