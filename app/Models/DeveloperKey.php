<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperKey extends Model
{
    use HasFactory;

    protected $fillable = [
        "key",
    ];

    protected $table = "developer_keys";

    function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "owner");
    }
}
