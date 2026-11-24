<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upt extends Model
{
    protected $table = 'upt';
    protected $fillable = ['name', 'type', 'work_area', 'address', 'province', 'city', 'contact', 'status', 'latitude', 'longitude', 'notes'];

    protected function casts(): array { return ['latitude' => 'float', 'longitude' => 'float']; }

    public function kerjasamas(): BelongsToMany
    {
        return $this->belongsToMany(Kerjasama::class, 'kerjasama_upt')->withPivot(['verification_status', 'verified_at', 'verified_by', 'verification_notes'])->withTimestamps();
    }

    public function kerjasamaAssignments(): HasMany { return $this->hasMany(KerjasamaUpt::class, 'upt_id'); }

    public function users(): HasMany { return $this->hasMany(User::class, 'upt_id'); }

    public function implementasis(): HasMany
    {
        return $this->hasMany(Implementasi::class);
    }
}
