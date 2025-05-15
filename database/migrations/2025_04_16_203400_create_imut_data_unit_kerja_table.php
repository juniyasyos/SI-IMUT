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
        Schema::create('imut_data_unit_kerja', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')->constrained('unit_kerja')->cascadeOnDelete();
            $table->foreignId('imut_data_id')->constrained('imut_data')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->primary(['unit_kerja_id', 'imut_data_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imut_data_unit_kerja');
    }
};
