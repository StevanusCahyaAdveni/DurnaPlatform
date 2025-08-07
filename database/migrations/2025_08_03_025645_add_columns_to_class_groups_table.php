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
        Schema::table('class_groups', function (Blueprint $table) {
            $table->string('price', 50)->nullable()->after('class_name'); // Menggunakan after() untuk posisi kolom
            $table->enum('subscription', ['monthly', 'yearly', 'one_time'])->nullable()->after('price');
            $table->string('participants', 100)->nullable()->after('subscription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropColumn(['price', 'subscription', 'participat']);
        });
    }
};
