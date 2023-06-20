<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fabric_year',
        'circulation_year',
        'tech_visit_expire',
        'gris_card',
        'assurance_card',
        'tech_visit',
        'type_id',
        'is_validated',
    ];

    protected $hidden = [
        'user_id',
        'type_id'
    ];

    #ONE TO MANY\INVERSE RELATIONSHIP(UN MOYENS DE TRANSPORT PEUT APPARTENIR A UN ET UN SEUL USER[celui qui a le role **is_transporter**])
    function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }

    #ONE TO MANY\INVERSE RELATIONSHIP(UN MOYENS DE TRANSPORT PEUT APPARTENIR A UN ET UN SEUL TYPE DE MOYEN DE TRANSPORT)
    function type() : BelongsTo {
        return $this->belongsTo(Type::class);
    }
}
