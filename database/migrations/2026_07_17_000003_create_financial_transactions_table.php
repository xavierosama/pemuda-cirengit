<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('financial_category_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('transaction_date');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'transaction_date']);
            $table->index('financial_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
