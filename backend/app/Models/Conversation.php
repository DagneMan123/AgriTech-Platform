<?php
// app/Models/Conversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_code',
        'user_1_id',
        'user_2_id',
        'conversation_type',
        'related_order_id',
        'related_delivery_id',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime'
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'user_1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user_2_id');
    }

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_1_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_2_id');
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
