<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ptn extends Model
{
    protected $table = 'ptn';
    protected $fillable = ['name', 'type', 'address', 'province', 'city', 'website', 'email', 'phone', 'notes'];

    public function kerjasamas(): HasMany
    {
        return $this->hasMany(Kerjasama::class);
    }
}
