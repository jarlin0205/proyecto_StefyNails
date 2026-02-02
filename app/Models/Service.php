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
     * Get duration in minutes from string.
     * Examples: "1 hora", "30 min", "1h", "2 horas"
     */
    public function getDurationInMinutesAttribute()
    {
        $duration = strtolower($this->duration);
        $minutes = 0;

        if (!$duration) {
            return 60;
        }

        // Check for 'hora' or 'h ' (handle decimals like 1.5)
        if (preg_match('/(\d+(\.\d+)?)\s*(hora|h)/', $duration, $matches)) {
            $minutes += (float)$matches[1] * 60;
        }
        
        // Check for 'min' or 'm ' (avoid double counting digits from hours)
        // If it sees "1 hora 30 min", we want to add 30.
        if (preg_match('/(\d+)\s*(min|m)/', $duration, $matches)) {
             $minutes += (int)$matches[1];
        }
        
        if ($minutes == 0) {
             if (is_numeric($duration)) {
                 $minutes = (int)$duration;
             } else {
                 // Try one more preg_match for just raw numbers if nothing else matched
                 preg_match('/(\d+)/', $duration, $matches);
                 $minutes = isset($matches[1]) ? (int)$matches[1] : 60;
             }
        }

        return (int)$minutes;
    }
}
