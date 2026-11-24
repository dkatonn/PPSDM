<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('implementasi', function (Blueprint $table) {
            $table->foreignId('ruang_lingkup_id')->nullable()->after('upt_id')->constrained('ruang_lingkup')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('implementasi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ruang_lingkup_id');
        });
    }
};
