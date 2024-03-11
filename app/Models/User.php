<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'phone',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    #ONE TO ONE/REVERSE RELATIONSHIP(UN UTILISATEUR NE PEUT QU'AVOIR UN SEUL RANG)
    function rang(): BelongsTo
    {
        return $this->belongsTo(Rang::class, 'rang_id');
    }

    #ONE TO MANY/INVERSE RELATIONSHIP (UN USER PEUT APPARTENIR A PLUISIEURS PROFILS)
    function profil(): BelongsTo
    {
        return $this->belongsTo(Profil::class, 'profil_id');
    }

    function drts(): HasMany
    {
        return $this->hasMany(Right::class, "user_id")->with(["action", "rang", "profil"]);
    }

    function affected_rights(): BelongsToMany
    {
        // return $this->belongsToMany(Right::class, "user_id")->with(["rang", "action", "profil"]);
        return $this->belongsToMany(Right::class, "rights_users", "user_id", "right_id")->with(["rang", "action", "profil"]);
    }

    function sold(): HasOne
    {
        return $this->hasOne(Solde::class, "owner")->with("manager")->where("visible", 1)->latest();
    }

    function expeditors(): HasMany
    {
        return $this->hasMany(Expeditor::class, "owner");
    }

    function campagne(): HasMany
    {
        return $this->hasMany(Campagne::class, "owner");
    }

    function developer(): HasOne
    {
        return $this->hasOne(DeveloperKey::class, "owner");
    }
}
