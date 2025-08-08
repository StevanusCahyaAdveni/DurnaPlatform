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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->decimal('amount', 15, 2);
            $table->enum('withdrawal_type', ['bank', 'ewallet']);

            // Bank withdrawal fields
            $table->string('bank_code')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();

            // E-wallet withdrawal fields
            $table->string('ewallet_type')->nullable(); // OVO, DANA, LINKAJA, SHOPEEPAY
            $table->string('phone_number')->nullable();

            // Status and tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('xendit_disbursement_id')->nullable();
            $table->string('external_id')->unique();
            $table->timestamp('processed_at')->nullable();

            // Financial details
            $table->decimal('admin_fee', 10, 2)->default(5000.00);
            $table->decimal('total_amount', 15, 2); // amount + admin_fee

            // Additional info
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key and indexes
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
