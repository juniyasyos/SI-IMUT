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
        Schema::create('imut_penilaians', function (Blueprint $table) {
            $table->id();
            // relationship
            $table->foreignId('imut_profil_id')->constrained('imut_profil')->onDelete('cascade');

            // fields imut penilaian
            $table->text('analysis')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('document_upload')->nullable();

            // fields imut penilaian calculation
            $table->decimal('numerator_value', 10, 2)->nullable();
            $table->decimal('denominator_value', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imut_penilaians');
    }
};
