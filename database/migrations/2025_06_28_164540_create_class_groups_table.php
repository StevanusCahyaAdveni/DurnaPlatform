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
        Schema::create('class_groups', function (Blueprint $table) {
            // Kolom ID menggunakan UUID sebagai primary key
            $table->uuid('id')->primary();
            $table->string('class_name', 250);
            $table->string('class_code', 10)->unique(); // class_code sebaiknya unique
            $table->text('class_description')->nullable(); // nullable jika deskripsi bisa kosong
            $table->string('class_category', 50)->default('public');
            // Foreign key ke tabel users (yang ID-nya juga UUID)
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('class_groups');
    }
};
