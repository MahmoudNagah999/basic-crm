<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['total', 'items', 'customer_id', 'status'])]
class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'status' => 'boolean',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
