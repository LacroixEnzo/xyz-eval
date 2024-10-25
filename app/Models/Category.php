<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['type']; // Permet de remplir automatiquement le champ 'name'

    // Relation : une catégorie a plusieurs tracks
    public function tracks()
    {
        return $this->hasMany(Track::class);
    }
}
