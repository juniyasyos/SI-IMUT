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
        Schema::create('imut_standar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imut_profile_id')->constrained('imut_profil')->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->string('description')->nullable();
            $table->date('start_period');
            $table->date('end_period');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imut_standar');
    }
};
