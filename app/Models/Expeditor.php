<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expeditor extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
    ];

    function status(): BelongsTo
    {
        return $this->belongsTo(ExpeditorStatus::class, "status");
    }

    function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, "owner");
    }
}
