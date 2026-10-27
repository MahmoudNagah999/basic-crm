<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_name', 'status', 'customer_id', 'project_cost'])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'project_cost' => 'decimal:2',
        ];
    }
}
