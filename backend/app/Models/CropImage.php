<?php
// app/Models/CropImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'crop_id',
        'image_path',
        'caption',
        'is_primary'
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}
