<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KerjasamaUpt extends Model
{
    protected $table = 'kerjasama_upt';
    public $incrementing = false;
    protected $primaryKey = null;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function kerjasama(): BelongsTo { return $this->belongsTo(Kerjasama::class); }
    public function upt(): BelongsTo { return $this->belongsTo(Upt::class); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
}
