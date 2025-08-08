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
        Schema::create('exam_question_option_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('option_id');
            $table->string('media_name', 250);
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('option_id')->references('id')->on('exam_question_options')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_question_option_media');
    }
};
