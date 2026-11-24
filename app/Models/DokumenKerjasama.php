<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenKerjasama extends Model
{
    protected $table = 'dokumen_kerjasama';
    protected $fillable = ['kerjasama_id', 'type', 'number', 'document_date', 'starts_at', 'ends_at', 'original_name', 'path', 'mime_type', 'file_size', 'is_public', 'uploaded_by'];
    protected function casts(): array { return ['document_date' => 'date', 'starts_at' => 'date', 'ends_at' => 'date', 'is_public' => 'boolean']; }
    public function kerjasama(): BelongsTo { return $this->belongsTo(Kerjasama::class); }
}
