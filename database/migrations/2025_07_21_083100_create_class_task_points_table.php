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
        Schema::create('class_task_points', function (Blueprint $table) {
            // Kolom ID sebagai UUID dan Primary Key
            $table->uuid('id')->primary();

            // Kolom task_id sebagai UUID, foreign key ke class_tasks
            $table->uuid('task_id');
            $table->foreign('task_id')->references('id')->on('class_tasks')->onDelete('cascade');

            // Kolom answer_id sebagai UUID, foreign key ke class_task_answers
            // Asumsi tabel 'class_task_answers' sudah ada atau akan dibuat
            $table->uuid('answer_id');
            $table->foreign('answer_id')->references('id')->on('class_task_answers')->onDelete('cascade');

            // Kolom user_id sebagai UUID, foreign key ke users (sebagai pembuat poin)
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Kolom point
            $table->string('point', 50);

            // Kolom created_at dan updated_at
            $table->timestamps();

            // Kolom deleted_at untuk Soft Deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_task_points');
    }
};
