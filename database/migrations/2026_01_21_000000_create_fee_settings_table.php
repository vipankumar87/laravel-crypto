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
        Schema::create('fee_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'platform_fee', 'transaction_fee'
            $table->string('label'); // Display label
            $table->decimal('value', 10, 4)->default(0); // Fee value
            $table->enum('type', ['percentage', 'flat'])->default('flat'); // Fee type
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default fee settings
        DB::table('fee_settings')->insert([
            [
                'name' => 'platform_fee',
                'label' => 'Platform Fee',
                'value' => 0,
                'type' => 'flat',
                'is_active' => true,
                'description' => 'Platform fee charged on investments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'transaction_fee',
                'label' => 'Transaction Fee',
                'value' => 0,
                'type' => 'flat',
                'is_active' => true,
                'description' => 'Transaction processing fee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_settings');
    }
};
