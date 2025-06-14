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

        Schema::create('region_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('imut_benchmarkings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imut_data_id')->constrained('imut_data')->onDelete('cascade');
            $table->foreignId('region_type_id')->constrained('region_types')->onDelete('restrict');
            $table->string('region_name')->nullable();
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('benchmark_value', 5, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imut_benchmarkings');
    }
};
