<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imut_penilaians', function (Blueprint $table) {
            $table->foreignId('laporan_unit_kerja_id')
                ->after('imut_profil_id')
                ->constrained('laporan_unit_kerjas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_penilaians', function (Blueprint $table) {
            $table->dropForeign(['laporan_unit_kerja_id']);
            $table->dropColumn('laporan_unit_kerja_id');
        });
    }
};
