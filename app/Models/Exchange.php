<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exchange extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class)->withPivot( 'updated_at');
    }
}
