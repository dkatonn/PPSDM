<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Implementasi extends Model
{
    protected $table = 'implementasi';
    protected $fillable = ['kerjasama_id', 'upt_id', 'ruang_lingkup_id', 'name', 'activity_date', 'description', 'status', 'created_by'];
    protected function casts(): array { return ['activity_date' => 'date']; }
    public function kerjasama(): BelongsTo { return $this->belongsTo(Kerjasama::class, 'kerjasama_id'); }
    public function upt(): BelongsTo { return $this->belongsTo(Upt::class); }
    public function ruangLingkup(): BelongsTo { return $this->belongsTo(RuangLingkup::class); }
    public function dokumen(): HasMany { return $this->hasMany(ImplementasiDokumen::class, 'implementasi_id'); }
}
