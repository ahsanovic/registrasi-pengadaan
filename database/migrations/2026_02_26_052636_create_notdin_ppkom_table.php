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
        Schema::create('notdin_ppkom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notdin_kpa_id')->constrained('notdin_kpa')->cascadeOnDelete();
            $table->string('nomor_agenda')->unique();
            $table->date('tanggal');
            $table->text('penyedia');
            $table->text('alamat');
            $table->string('npwp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notdin_ppkom');
    }
};
