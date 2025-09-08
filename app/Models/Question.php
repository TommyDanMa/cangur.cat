<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['title','statement','year','level','image_path','phase','session','created_by'];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
