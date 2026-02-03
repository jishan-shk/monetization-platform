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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('subscription_tier_id')->nullable()->after('api_key')->constrained();
            $table->enum('role', [ROLE_ADMIN, ROLE_DEVELOPER])->default(ROLE_DEVELOPER)->after('api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subscription_tier_id']);
            $table->dropColumn('subscription_tier_id');
            $table->dropColumn('role');
        });
    }
};
