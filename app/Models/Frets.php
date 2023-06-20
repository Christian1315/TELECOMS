<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Frets extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'nature',
        'vol_or_quant',
        'charg_date',
        'charg_location',
        'charg_destination',
        'axles_num',
        'fret_img',
    ];

    protected $hidden = [
        'user_id',
    ];

    #ONE TO MANY\INVERSE RELATIONSHIP(UN FRET PEUT APPARTENIR A UN ET UN SEUL USER[celui qui a le role **is_sender**])
    function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
