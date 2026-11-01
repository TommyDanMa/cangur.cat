<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'question_id', 'format', 'content_text', 'video_url', 'image_path',
        'solution', 'no_solution'
    ];

    protected $casts = [
        'no_solution' => 'boolean',
        'solution' => 'integer',
    ];

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function question(): belongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
