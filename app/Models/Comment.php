<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    public function beaches()
    {

        return $this->belongsTo(Beaches::class, 'beach_id');
    }
    public function account()
    {

        return $this->belongsTo(Account::class, 'accout_id');
    }
    public function content()
    {

        return $this->belongsTo(Contents::class, 'content_id');
    }
   
}