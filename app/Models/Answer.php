<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'user_id', 'question_id', 'format', 'content_text', 'video_url', 'image_path',
        'solution', 'no_solution'
    ];

    protected $casts = [
        'no_solution' => 'boolean',
        'solution' => 'integer',
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function question(){ return $this->belongsTo(Question::class); }
}
