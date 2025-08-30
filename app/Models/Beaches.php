<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beaches extends Model
{
    use HasFactory;
    public function region() {

        return $this->belongsTo(Regions::class, 'region_id');
    }
    public function images()
    {
        return $this->hasMany(ImageBeaches::class, 'beach_id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'beach_id');
    }
    public function favorites()
    {
        return $this->hasMany(Favorites::class, 'beach_id');
    }
}