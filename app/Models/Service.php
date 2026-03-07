<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'price_display',
        'duration',
        'duration_in_minutes',
        'image_path',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function images()
    {
        return $this->hasMany(ServiceImage::class);
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
     * Get duration in minutes.
     * Prioritizes the numeric column, falls back to parsing string if needed.
     */
    public function getDurationInMinutesAttribute($value)
    {
        if ($value) {
            return (int)$value;
        }

        $duration = strtolower($this->duration);
        $minutes = 0;

        if (!$duration) {
            return 60;
        }

        // Fallback parsing (mostly for objects created only in memory or edge cases)
        if (preg_match('/(\d+(\.\d+)?)\s*(hora|h)/', $duration, $matches)) {
            $minutes += (float)$matches[1] * 60;
        }
        
        if (preg_match('/(\d+)\s*(min|m)/', $duration, $matches)) {
             $minutes += (int)$matches[1];
        }
        
        if ($minutes == 0) {
             if (is_numeric($duration)) {
                 $minutes = (int)$duration;
             } else {
                 preg_match('/(\d+)/', $duration, $matches);
                 $minutes = isset($matches[1]) ? (int)$matches[1] : 60;
             }
        }

        return (int)$minutes;
    }
}
