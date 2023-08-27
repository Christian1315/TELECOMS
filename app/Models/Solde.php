<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solde extends Model
{
    use HasFactory;

    protected $fillable = [
        "solde_amount",
    ];

    function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, "owner");
    }

    function owner_phone(): BelongsTo
    {
        return $this->belongsTo(User::class, "owner")->withDefault("phone");
    }

    function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, "manager");
    }

    function manager_with_name(): BelongsTo
    {
        return $this->belongsTo(User::class, "manager")->withDefault("firstname");
    }
}
