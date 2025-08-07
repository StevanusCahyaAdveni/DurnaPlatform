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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('tipe', ['class', 'course']);

            // Kolom untuk foreign key, salah satu harus null
            $table->uuid('class_uuid')->nullable();
            $table->foreign('class_uuid')->references('id')->on('class_groups')->onDelete('cascade');

            $table->uuid('course_uuid')->nullable();
            $table->foreign('course_uuid')->references('id')->on('courses')->onDelete('cascade');

            // Foreign key ke tabel users sebagai subscriber
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('nominal', 50);
            $table->string('payment_method', 50);
            $table->datetime('expired_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
