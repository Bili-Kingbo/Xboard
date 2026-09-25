<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v2_server', function (Blueprint $table): void {
            $table->json('client_routing_profile_ids')->nullable();
        });

        Schema::table('v2_special_server', function (Blueprint $table): void {
            $table->json('client_routing_profile_ids')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('v2_server', function (Blueprint $table): void {
            $table->dropColumn('client_routing_profile_ids');
        });

        Schema::table('v2_special_server', function (Blueprint $table): void {
            $table->dropColumn('client_routing_profile_ids');
        });
    }
};
