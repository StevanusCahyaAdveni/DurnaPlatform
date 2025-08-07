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
        Schema::create('course_videos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign key ke tabel courses
            $table->uuid('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');

            $table->string('video_title', 250);
            $table->string('video_description', 250);
            $table->string('video_name', 250);
            $table->string('video_thumbnail', 500);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_videos');
    }
};
