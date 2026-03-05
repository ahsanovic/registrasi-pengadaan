<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('username', 'users_username_idx');
        });

        Schema::table('ref_bidang', function (Blueprint $table) {
            $table->index('parent_id', 'ref_bidang_parent_id_idx');
        });

        Schema::table('notdin_kpa', function (Blueprint $table) {
            $table->index('tanggal', 'notdin_kpa_tanggal_idx');
        });

        Schema::table('notdin_ppkom', function (Blueprint $table) {
            $table->index('tanggal', 'notdin_ppkom_tanggal_idx');
        });

        Schema::table('dokumen_pengadaan', function (Blueprint $table) {
            $table->index('tanggal', 'dokumen_pengadaan_tanggal_idx');
        });

        Schema::table('space_nomor', function (Blueprint $table) {
            $table->index(['tahun', 'nomor_agenda'], 'space_nomor_tahun_nomor_idx');
            $table->index(['tahun', 'used_at', 'tanggal', 'nomor_agenda'], 'space_nomor_tahun_used_tanggal_nomor_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('space_nomor', function (Blueprint $table) {
            $table->dropIndex('space_nomor_tahun_used_tanggal_nomor_idx');
            $table->dropIndex('space_nomor_tahun_nomor_idx');
        });

        Schema::table('dokumen_pengadaan', function (Blueprint $table) {
            $table->dropIndex('dokumen_pengadaan_tanggal_idx');
        });

        Schema::table('notdin_ppkom', function (Blueprint $table) {
            $table->dropIndex('notdin_ppkom_tanggal_idx');
        });

        Schema::table('notdin_kpa', function (Blueprint $table) {
            $table->dropIndex('notdin_kpa_tanggal_idx');
        });

        Schema::table('ref_bidang', function (Blueprint $table) {
            $table->dropIndex('ref_bidang_parent_id_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_username_idx');
        });
    }
};
