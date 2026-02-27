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
        'payment_method',
        'cash_amount',
        'transfer_amount',
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
            get: fn (string $value) => ucfirst(strtolower($value)),
            set: fn (string $value) => ucfirst(strtolower($value)),
        );
    }
}
