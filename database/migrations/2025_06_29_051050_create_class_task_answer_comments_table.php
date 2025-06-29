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
        Schema::create('class_task_answer_comments', function (Blueprint $table) {
            // Kolom ID menggunakan UUID sebagai primary key
            $table->uuid('id')->primary();
            // Foreign key ke tabel class_task_answers
            $table->foreignUuid('answer_id')->constrained('class_task_answers')->onDelete('cascade');
            // Foreign key ke tabel users (yang ID-nya UUID)
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // ID user yang membuat komentar
            $table->string('comment_media', 500)->nullable(); // Media untuk komentar, nullable jika tidak selalu ada
            $table->text('comment_text')->nullable(); // Teks komentar, nullable jika hanya ada media
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
        Schema::dropIfExists('class_task_answer_comments');
    }
};
