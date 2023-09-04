<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campagne extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "group",
        "date",
        "start_date",
        "end_date",
        "num_time_by_day",
        "expeditor",
        "sms_type",
        "message",
        "sms_send_frequency",
    ];

    function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, "group");
    }

    function status(): BelongsTo
    {
        return $this->belongsTo(CampagneStatus::class, "status");
    }

    function groupes(): BelongsToMany
    {
        return $this->belongsToMany(Groupe::class, "campagnes_groupes", "groupe_id", "campagne_id")->with("contacts");
    }

    function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, "owner");
    }

    function expeditor(): BelongsTo
    {
        return $this->belongsTo(Expeditor::class, "expeditor");
    }
}
