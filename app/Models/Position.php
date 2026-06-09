<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
