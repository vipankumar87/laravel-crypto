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
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('from_address', 255);
            $table->string('to_address', 255);
            $table->string('amount', 255);
            $table->string('token', 10)->default("N/a");
            $table->enum('status', ['received', 'transferred']);
            $table->string('tx_hash', 500);
            $table->unsignedBigInteger('invests_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('block_number', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
    }
};
