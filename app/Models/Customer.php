<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Customer extends Model
{
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
