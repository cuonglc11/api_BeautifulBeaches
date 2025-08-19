<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contents extends Model
{
    use HasFactory;
    public function beaches()
    {

        return $this->belongsTo(Beaches::class, 'beach_id');
    }
}
