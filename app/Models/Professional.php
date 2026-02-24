<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'specialty',
        'phone',
        'photo_path',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Mutator para estandarizar el nombre
     */
    protected function name(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn (string $value) => \Illuminate\Support\Str::title($value),
            set: fn (string $value) => \Illuminate\Support\Str::title($value),
        );
    }

    /**
     * Mutator para la especialidad
     */
    protected function specialty(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn (?string $value) => $value ? \Illuminate\Support\Str::title($value) : null,
            set: fn (?string $value) => $value ? \Illuminate\Support\Str::title($value) : null,
        );
    }
}
