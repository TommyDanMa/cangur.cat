<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    public function questions(){ return $this->hasMany(Question::class); }
}
