<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Kerjasama extends Model
{
    protected $table = 'kerjasama';
    protected $fillable = ['ptn_id', 'title', 'agreement_type', 'document_number', 'signed_at', 'starts_at', 'ends_at', 'status', 'notes', 'created_by'];
    protected function casts(): array { return ['signed_at' => 'date', 'starts_at' => 'date', 'ends_at' => 'date']; }

    public function scopeWithSignedCopy(Builder $query): Builder
    {
        return $query->whereNotNull('signed_at')->whereHas('dokumen', fn ($docs) => $docs->whereIn('type', ['ka', 'pks']));
    }

    public function scopeVerifiedByUpt(Builder $query): Builder
    {
        return $query->whereHas('upt', fn ($upts) => $upts->where('kerjasama_upt.verification_status', 'active'));
    }

    public function hasActiveUpt(): bool
    {
        return $this->upt()->wherePivot('verification_status', 'active')->exists();
    }

    public function hasSignedCopy(): bool
    {
        return $this->signed_at !== null && $this->dokumen()->whereIn('type', ['ka', 'pks'])->exists();
    }

    public function ptn(): BelongsTo { return $this->belongsTo(Ptn::class); }
    public function upt(): BelongsToMany { return $this->belongsToMany(Upt::class, 'kerjasama_upt')->withPivot(['verification_status', 'verified_at', 'verified_by', 'verification_notes'])->withTimestamps(); }
    public function ruangLingkup(): BelongsToMany { return $this->belongsToMany(RuangLingkup::class, 'kerjasama_ruang_lingkup'); }
    public function uptAssignments(): HasMany { return $this->hasMany(KerjasamaUpt::class, 'kerjasama_id'); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenKerjasama::class, 'kerjasama_id'); }
    public function implementasis(): HasMany { return $this->hasMany(Implementasi::class, 'kerjasama_id'); }
    public function auditLogs(): HasMany { return $this->hasMany(AuditLog::class, 'subject_id')->where('subject_type', $this->getMorphClass()); }
}
