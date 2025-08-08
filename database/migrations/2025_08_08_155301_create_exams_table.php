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
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('exam_name', 250);
            $table->text('exam_description');
            $table->datetime('exam_deadline');
            $table->uuid('classgroup_id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('classgroup_id')->references('id')->on('class_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
