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
        'professional_id',
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

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    /**
     * Mutator para el nombre del cliente
     */
    protected function customerName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn (string $value) => \Illuminate\Support\Str::title($value),
        );
    }

    /**
     * Mutator para las notas (Primera letra de la frase en mayúscula)
     */
    protected function notes(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn (?string $value) => $value ? ucfirst(strtolower($value)) : null,
        );
    }

    /**
     * Mutator para el motivo de reprogramación
     */
    protected function rescheduleReason(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn (?string $value) => $value ? ucfirst(strtolower($value)) : null,
        );
    }
}
