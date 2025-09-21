<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['title','statement','year','level','image_path','phase','session','created_by','block_id','position'];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }
}
