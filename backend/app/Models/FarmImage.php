<?php
// app/Models/FarmImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'image_path',
        'caption',
        'is_primary'
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}
