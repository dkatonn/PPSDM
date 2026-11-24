<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptn', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->nullable();
            $table->text('address')->nullable();
            $table->string('province')->index();
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('upt', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->nullable();
            $table->string('work_area')->nullable();
            $table->text('address')->nullable();
            $table->string('province')->index();
            $table->string('city')->nullable();
            $table->string('contact')->nullable();
            $table->string('status', 30)->default('operasional')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ruang_lingkup', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('kerjasama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ptn_id')->constrained('ptn')->restrictOnDelete();
            $table->string('title');
            $table->string('agreement_type', 30)->default('pks')->index();
            $table->string('document_number')->nullable()->index();
            $table->date('signed_at')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable()->index();
            $table->string('status', 30)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kerjasama_upt', function (Blueprint $table) {
            $table->foreignId('kerjasama_id')->constrained('kerjasama')->cascadeOnDelete();
            $table->foreignId('upt_id')->constrained('upt')->restrictOnDelete();
            $table->primary(['kerjasama_id', 'upt_id']);
        });

        Schema::create('kerjasama_ruang_lingkup', function (Blueprint $table) {
            $table->foreignId('kerjasama_id')->constrained('kerjasama')->cascadeOnDelete();
            $table->foreignId('ruang_lingkup_id')->constrained('ruang_lingkup')->restrictOnDelete();
            $table->primary(['kerjasama_id', 'ruang_lingkup_id']);
        });

        Schema::create('dokumen_kerjasama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kerjasama_id')->constrained('kerjasama')->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->string('number')->nullable();
            $table->date('document_date')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('implementasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kerjasama_id')->constrained('kerjasama')->cascadeOnDelete();
            $table->foreignId('upt_id')->nullable()->constrained('upt')->nullOnDelete();
            $table->string('name');
            $table->date('activity_date')->index();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('terencana')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('implementasi_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('implementasi_id')->constrained('implementasi')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('implementasi_dokumen');
        Schema::dropIfExists('implementasi');
        Schema::dropIfExists('dokumen_kerjasama');
        Schema::dropIfExists('kerjasama_ruang_lingkup');
        Schema::dropIfExists('kerjasama_upt');
        Schema::dropIfExists('kerjasama');
        Schema::dropIfExists('ruang_lingkup');
        Schema::dropIfExists('upt');
        Schema::dropIfExists('ptn');
    }
};
