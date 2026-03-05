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
        Schema::create('notdin_kpa', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_agenda')->unique();
            $table->string('bidang_id');
            $table->foreign('bidang_id')->references('id')->on('ref_bidang');
            $table->date('tanggal');
            $table->string('program');
            $table->string('kegiatan');
            $table->string('mata_anggaran');
            $table->text('rencana_kegiatan');
            $table->decimal('rencana_anggaran', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notdin_kpa');
    }
};
