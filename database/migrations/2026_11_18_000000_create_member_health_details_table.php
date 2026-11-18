<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per checked category. Unchecking a category sets is_active = false;
        // the answers are kept, never deleted.
        Schema::create('member_health_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('member', 'member_id')->onDelete('cascade');
            $table->string('group', 32);    // disabilities | food_allergies | medical_conditions
            $table->string('category', 32); // mobility, visual, ..., dairy, nuts, ..., diabetes, other
            $table->json('answers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['member_id', 'group', 'category']);
        });

        Schema::table('member', function (Blueprint $table) {
            $table->timestamp('health_details_completed_at')->nullable()->after('member_status');
        });
    }

    public function down(): void
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn('health_details_completed_at');
        });

        Schema::dropIfExists('member_health_details');
    }
};
