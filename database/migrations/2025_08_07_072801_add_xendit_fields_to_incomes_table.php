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
        Schema::table('incomes', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->nullable()->after('status');
            $table->text('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            $table->string('payment_channel')->nullable()->after('xendit_invoice_url');
            $table->timestamp('paid_at')->nullable()->after('payment_channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn(['xendit_invoice_id', 'xendit_invoice_url', 'payment_channel', 'paid_at']);
        });
    }
};
