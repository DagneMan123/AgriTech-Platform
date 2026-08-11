<?php
// app/Models/TrainingMaterial.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_id',
        'title',
        'description',
        'type',
        'file_path',
        'video_url',
        'thumbnail',
        'tags',
        'is_published'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_published' => 'boolean'
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function getTypeLabelAttribute()
    {
        $types = [
            'pdf' => 'PDF Document',
            'video' => 'Video',
            'article' => 'Article',
            'presentation' => 'Presentation'
        ];
        return $types[$this->type] ?? ucfirst($this->type);
    }
}
