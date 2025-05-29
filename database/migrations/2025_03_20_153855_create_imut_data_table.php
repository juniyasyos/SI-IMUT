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
        Schema::create('imut_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('category_name', 100)->unique();
            $table->string('short_name', 20)->nullable();
            $table->enum('scope', ['global', 'internal', 'unit'])->default('internal');
            $table->string('description', length: 255)->nullable();
            $table->boolean('is_use_global')->default(false);
            $table->boolean('is_benchmark_category')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('imut_data', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->unique();
            $table->foreignId('imut_kategori_id')->constrained('imut_kategori')->cascadeOnDelete();
            $table->string('slug', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('imut_profil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imut_data_id')->constrained('imut_data')->cascadeOnDelete()->nullable();
            $table->string('slug', 255)->nullable()->unique();
            $table->string('version', 50)->default('version 1');
            $table->text('rationale')->nullable();
            $table->text('quality_dimension')->nullable();
            $table->text('objective')->nullable();
            $table->text('operational_definition')->nullable();
            $table->enum('indicator_type', ['process', 'output', 'outcome'])->nullable();
            $table->text('numerator_formula')->nullable();
            $table->text('denominator_formula')->nullable();
            $table->text('inclusion_criteria')->nullable();
            $table->text('exclusion_criteria')->nullable();
            $table->string('data_source', 255)->nullable();
            $table->string('data_collection_frequency', 255)->nullable();
            $table->text('analysis_plan')->nullable();
            $table->enum('target_operator', ['=', '>=', '<=', '<', '>'])->default('>=')->nullable();
            $table->bigInteger('target_value')->nullable();
            $table->enum('analysis_period_type', ['mingguan', 'bulanan']);
            $table->bigInteger('analysis_period_value')->nullable();
            $table->date('start_period')->nullable();
            $table->date('end_period')->nullable();
            $table->string('data_collection_method', 255)->nullable();
            $table->string('sampling_method', 255)->nullable();
            $table->text('data_collection_tool')->nullable();
            $table->string('responsible_person', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imut_profil');
        Schema::dropIfExists('imut_data');
        Schema::dropIfExists('imut_kategori');
    }
};
