<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'customer_name',
        'customer_phone',
        'location',
        'offered_price',
        'appointment_date',
        'status',
        'notes',
        'reference_image_path',
        'reschedule_reason',
        'reschedule_token',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->reschedule_token)) {
                $model->reschedule_token = \Illuminate\Support\Str::random(32);
            }
        });
    }

    public function getRescheduleTokenAttribute($value)
    {
        if (empty($value)) {
            $newToken = \Illuminate\Support\Str::random(32);
            $this->attributes['reschedule_token'] = $newToken;
            $this->save();
            return $newToken;
        }
        return $value;
    }

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
