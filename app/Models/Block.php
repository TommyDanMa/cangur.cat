<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $fillable = ['name','description','parent_id','position','year','level','visible'];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Block::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Block::class, 'parent_id')->orderBy('position');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('position')->orderBy('created_at');
    }

    // Simple scopes for filters (keep optional and KISS)
    public function scopeFilter($q, array $filters)
    {
        if (!empty($filters['year'])) $q->where('year', (int) $filters['year']);
        if (!empty($filters['level'])) $q->where('level', $filters['level']);
        if (isset($filters['visible']) && $filters['visible'] !== '') $q->where('visible', (bool) $filters['visible']);
        return $q;
    }
}
