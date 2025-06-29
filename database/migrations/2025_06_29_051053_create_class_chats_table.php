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
        Schema::create('class_chats', function (Blueprint $table) {
            // Kolom ID menggunakan UUID sebagai primary key
            $table->uuid('id')->primary();
            // Foreign key ke tabel class_groups (yang ID-nya UUID)
            $table->foreignUuid('class_group_id')->constrained('class_groups')->onDelete('cascade');
            // Foreign key ke tabel users (yang ID-nya UUID)
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // ID user yang membuat chat
            $table->string('class_chat_id', 50)->nullable(); // ID untuk merujuk chat induk (untuk komentar), nullable
            $table->string('chat_media', 500)->nullable(); // Media untuk chat, nullable jika tidak selalu ada
            $table->text('chat_text')->nullable(); // Teks chat, nullable jika hanya ada media
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
        Schema::dropIfExists('class_chats');
    }
};
