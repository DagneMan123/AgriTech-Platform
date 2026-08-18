<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmImage extends Model
{
    use HasFactory;

    protected $fillable = ['farm_id', 'image_path', 'description'];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
