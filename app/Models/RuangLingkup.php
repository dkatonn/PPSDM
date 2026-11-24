<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RuangLingkup extends Model
{
    protected $table = 'ruang_lingkup';
    protected $fillable = ['name', 'description'];

    public function kerjasamas(): BelongsToMany
    {
        return $this->belongsToMany(Kerjasama::class, 'kerjasama_ruang_lingkup');
    }
}
