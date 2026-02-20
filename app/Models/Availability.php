<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = ['date', 'active_slots', 'message', 'professional_id'];

    protected $casts = [
        'active_slots' => 'array'
    ];

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
