<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImplementasiDokumen extends Model
{
    protected $table = 'implementasi_dokumen';
    protected $fillable = ['implementasi_id', 'original_name', 'path', 'mime_type', 'file_size', 'uploaded_by'];
    public function implementasi(): BelongsTo { return $this->belongsTo(Implementasi::class, 'implementasi_id'); }
}
