<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kerjasama_id')->constrained('kerjasama')->cascadeOnDelete();
            $table->date('reminder_on');
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'kerjasama_id', 'reminder_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
