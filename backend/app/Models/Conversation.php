<?php
// app/Models/Conversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_at'
    ];

    protected $casts = ['last_message_at' => 'datetime'];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }
}
