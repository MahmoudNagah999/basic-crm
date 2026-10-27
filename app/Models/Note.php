<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['note', 'customer_id'])]
class Note extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
