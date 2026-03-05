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
        Schema::create('space_nomor', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->date('tanggal');
            $table->bigInteger('nomor_agenda');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->unique(['tahun', 'tanggal', 'nomor_agenda']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_nomor');
    }
};
