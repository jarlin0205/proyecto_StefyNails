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
            // No guardar aquí para evitar recursión o duplicados durante la creación.
            // El evento 'creating' ya se encarga de los registros nuevos.
            return null;
        }
        return $value;
    }

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    public function getFinalPriceAttribute()
    {
        return $this->offered_price ?? ($this->service ? $this->service->price : 0);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
