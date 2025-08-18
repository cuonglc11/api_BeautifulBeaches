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
}