<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $fillable = ['user_id', 'kerjasama_id', 'reminder_on', 'title', 'message', 'read_at'];
    protected function casts(): array { return ['reminder_on' => 'date', 'read_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function kerjasama(): BelongsTo { return $this->belongsTo(Kerjasama::class); }
}
