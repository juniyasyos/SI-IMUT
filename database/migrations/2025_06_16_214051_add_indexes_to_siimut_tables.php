<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imut_penilaians', function (Blueprint $table) {
            $table->index('imut_profil_id', 'idx_imut_penilaians_profil_id');
            $table->index('laporan_unit_kerja_id', 'idx_imut_penilaians_laporan_unit_id');
        });

        Schema::table('laporan_unit_kerjas', function (Blueprint $table) {
            $table->index('laporan_imut_id', 'idx_laporan_unit_kerjas_laporan_id');
        });

        Schema::table('imut_profil', function (Blueprint $table) {
            $table->index('imut_data_id', 'idx_imut_profil_data_id');
            $table->index(['imut_data_id', 'version'], 'idx_imut_profil_data_version');
        });

        Schema::table('imut_data', function (Blueprint $table) {
            $table->index('status', 'idx_imut_data_status');
        });
    }

    public function down(): void
    {
        Schema::table('imut_penilaians', function (Blueprint $table) {
            $table->dropIndex('idx_imut_penilaians_profil_id');
            $table->dropIndex('idx_imut_penilaians_laporan_unit_id');
        });

        Schema::table('laporan_unit_kerjas', function (Blueprint $table) {
            $table->dropIndex('idx_laporan_unit_kerjas_laporan_id');
        });

        Schema::table('imut_profil', function (Blueprint $table) {
            $table->dropIndex('idx_imut_profil_data_id');
            $table->dropIndex('idx_imut_profil_data_version');
        });

        Schema::table('imut_data', function (Blueprint $table) {
            $table->dropIndex('idx_imut_data_status');
        });
    }
};
