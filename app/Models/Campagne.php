<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campagne extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "group",
        "date",
        "end_date",
        "num_time_by_day",
        "expeditor",
        "sms_type",
        "message",
    ];

    function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, "group");
    }
}
