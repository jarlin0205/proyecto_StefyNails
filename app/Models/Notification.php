<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'title',
        'message',
        'is_read',
        'type',
        'action_url',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
