<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    //
    protected $fillable = [
        'description',
        'amount',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Mutator para la descripción (Primera letra en mayúscula)
     */
    protected function description(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn (string $value) => ucfirst(strtolower($value)),
        );
    }
}
