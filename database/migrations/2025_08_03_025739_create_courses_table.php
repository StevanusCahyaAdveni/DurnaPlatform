<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('course_name', 250);
            $table->string('course_code', 10);
            $table->text('course_description');
            $table->string('course_categori', 50)->default('public');

            // Foreign key ke tabel users sebagai pembuat kursus
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('price', 50);
            $table->string('course_thumbnail', 500);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
