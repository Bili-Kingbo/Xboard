<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('v2_server_group', 'transfer_enable')) {
            return;
        }

        Schema::table('v2_server_group', function (Blueprint $table) {
            $table->dropColumn('transfer_enable');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('v2_server_group', 'transfer_enable')) {
            return;
        }

        Schema::table('v2_server_group', function (Blueprint $table) {
            $table->unsignedBigInteger('transfer_enable')
                ->default(0)
                ->after('name')
                ->comment('权限组流量额度（字节）');
        });
    }
};
