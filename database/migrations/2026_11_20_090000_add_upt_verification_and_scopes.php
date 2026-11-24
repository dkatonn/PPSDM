<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('upt_id')->nullable()->after('role')->constrained('upt')->nullOnDelete();
            $table->index(['role', 'upt_id']);
        });

        Schema::table('kerjasama_upt', function (Blueprint $table) {
            $table->string('verification_status', 30)->default('pending')->after('upt_id')->index();
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable()->after('verified_by');
            $table->timestamps();
        });

        Schema::create('kerjasama_upt_ruang_lingkup', function (Blueprint $table) {
            $table->foreignId('kerjasama_id')->constrained('kerjasama')->cascadeOnDelete();
            $table->foreignId('upt_id')->constrained('upt')->cascadeOnDelete();
            $table->foreignId('ruang_lingkup_id')->constrained('ruang_lingkup')->restrictOnDelete();
            $table->primary(['kerjasama_id', 'upt_id', 'ruang_lingkup_id'], 'ku_scope_primary');
        });

        // Preserve previously recorded scopes by associating them with the UPTs
        // already linked to each agreement.
        DB::table('kerjasama_upt')->orderBy('kerjasama_id')->orderBy('upt_id')->get()->each(function ($assignment): void {
            $scopeIds = DB::table('kerjasama_ruang_lingkup')
                ->where('kerjasama_id', $assignment->kerjasama_id)
                ->pluck('ruang_lingkup_id');

            foreach ($scopeIds as $scopeId) {
                DB::table('kerjasama_upt_ruang_lingkup')->insertOrIgnore([
                    'kerjasama_id' => $assignment->kerjasama_id,
                    'upt_id' => $assignment->upt_id,
                    'ruang_lingkup_id' => $scopeId,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerjasama_upt_ruang_lingkup');

        Schema::table('kerjasama_upt', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn(['verification_status', 'verified_at', 'verified_by', 'verification_notes', 'created_at', 'updated_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'upt_id']);
            $table->dropForeign(['upt_id']);
            $table->dropColumn('upt_id');
        });
    }
};
