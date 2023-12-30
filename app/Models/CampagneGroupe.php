<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampagneGroupe extends Model
{
    use HasFactory;

    protected $table = "campagnes_groupes";

    protected $fillable = [
        "groupe_id_new",
        "campagne_id"
    ];
}
