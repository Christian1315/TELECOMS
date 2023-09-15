<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DifferedSms extends Model
{
    use HasFactory;

    protected $fillable = [
        "send_date",
        "contact",
        "group",
        "send_date",
        "message",
        'expediteur'
    ];
}
