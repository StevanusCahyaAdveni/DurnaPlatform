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
        Schema::create('exam_question_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_answer_id');
            $table->uuid('question_id');
            $table->uuid('user_id');
            $table->text('answer_text');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('exam_answer_id')->references('id')->on('exam_answers')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('exam_questions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_question_answers');
    }
};
