<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    public function contacts(): BelongsToMany
    {
        return $this->BelongsToMany(Contact::class, 'contacts_groupes', 'groupe_id', 'contact_id')->orderBy("id", "desc");
    }

    function campagnes(): BelongsToMany
    {
        return $this->belongsToMany(Campagne::class, "campagnes_groupes", "campagne_id", "groupe_id");
    }

    function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, "owner");
    }
}
