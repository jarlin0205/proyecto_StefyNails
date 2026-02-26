<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_category_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_product')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }
}
