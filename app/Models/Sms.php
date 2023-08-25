<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sms extends Model
{
    use HasFactory;

    protected $fillable = [
        "messageId",
        "from",
        "to",
        "message",
        "type",
        "route",
        "sms_count",
        "amount",
        "currency",
        // "status"
    ];

    function status(): BelongsTo
    {
        return $this->belongsTo(SmsStatus::class, "status");
    }
}
