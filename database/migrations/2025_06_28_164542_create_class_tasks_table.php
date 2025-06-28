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
        Schema::create('class_tasks', function (Blueprint $table) {
            // Kolom ID menggunakan UUID sebagai primary key
            $table->uuid('id')->primary();
            $table->string('task_name', 250);
            $table->text('task_description')->nullable(); // nullable jika deskripsi bisa kosong
            $table->text('task_deadline')->nullable(); // Menggunakan 'text' sesuai permintaan, meskipun 'datetime' lebih umum untuk deadline
            // Foreign key ke tabel class_groups (yang ID-nya UUID)
            $table->foreignUuid('class_group_id')->constrained('class_groups')->onDelete('cascade');
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
        Schema::dropIfExists('class_tasks');
    }
};
