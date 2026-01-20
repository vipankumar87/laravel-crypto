<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('referral_level_settings')) {
            return;
        }

        Schema::create('referral_level_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level')->unique();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default levels
        $defaultLevels = [
            ['level' => 1, 'percentage' => 10.00, 'is_active' => true, 'description' => 'Level 1 - Direct Referral'],
            ['level' => 2, 'percentage' => 5.00, 'is_active' => true, 'description' => 'Level 2'],
            ['level' => 3, 'percentage' => 3.00, 'is_active' => true, 'description' => 'Level 3'],
            ['level' => 4, 'percentage' => 2.00, 'is_active' => true, 'description' => 'Level 4'],
            ['level' => 5, 'percentage' => 1.00, 'is_active' => true, 'description' => 'Level 5'],
        ];

        foreach ($defaultLevels as $level) {
            DB::table('referral_level_settings')->insert(array_merge($level, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_level_settings');
    }
};
