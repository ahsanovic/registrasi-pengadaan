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
        Schema::table('notdin_ppkom', function (Blueprint $table) {
            $table->dropForeign(['notdin_kpa_id']);
            $table->foreign('notdin_kpa_id')
                ->references('id')
                ->on('notdin_kpa')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notdin_ppkom', function (Blueprint $table) {
            $table->dropForeign(['notdin_kpa_id']);
            $table->foreign('notdin_kpa_id')
                ->references('id')
                ->on('notdin_kpa');
        });
    }
};
