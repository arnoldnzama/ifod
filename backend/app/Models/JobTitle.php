<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'category'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
