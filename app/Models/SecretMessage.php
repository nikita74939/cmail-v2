<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecretMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'stego_image_path',
        'original_image_path',
    ];

    /**
     * Relasi ke User (sender)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relasi ke User (receiver)
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}