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
        Schema::create('dokumen_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notdin_ppkom_id')->constrained('notdin_ppkom')->cascadeOnDelete();
            $table->string('nomor_agenda')->unique();
            $table->date('tanggal');
            $table->foreignId('jenis_dokumen_id')->constrained('ref_jenis_dokumen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_pengadaan');
    }
};
