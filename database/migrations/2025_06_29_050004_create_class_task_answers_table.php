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
        Schema::create('class_task_answers', function (Blueprint $table) {
            // Kolom ID menggunakan UUID sebagai primary key
            $table->uuid('id')->primary();
            // Foreign key ke tabel class_tasks (yang ID-nya UUID)
            $table->foreignUuid('task_id')->constrained('class_tasks')->onDelete('cascade');
            // Foreign key ke tabel users (yang ID-nya UUID)
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // ID user yang menjawab
            $table->text('answer_text')->nullable(); // Jawaban bisa berupa teks, nullable jika bisa hanya media
            $table->timestamps();
            // Menambahkan kolom untuk soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_task_answers');
    }
};
