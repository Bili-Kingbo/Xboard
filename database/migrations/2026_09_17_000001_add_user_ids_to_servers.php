<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v2_server', function (Blueprint $table) {
            $table->json('user_ids')->nullable()->after('group_ids');
        });

        Schema::table('v2_special_server', function (Blueprint $table) {
            $table->json('user_ids')->nullable()->after('group_ids');
        });
    }

    public function down(): void
    {
        Schema::table('v2_server', function (Blueprint $table) {
            $table->dropColumn('user_ids');
        });

        Schema::table('v2_special_server', function (Blueprint $table) {
            $table->dropColumn('user_ids');
        });
    }
};
