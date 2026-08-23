<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('v2_special_server', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 32);
            $table->json('group_ids')->nullable();
            $table->json('tags')->nullable();
            $table->json('proxy_payload');
            $table->string('source_type', 24)->default('content');
            $table->string('source_label')->nullable();
            $table->boolean('show')->default(true)->index();
            $table->unsignedInteger('sort')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('v2_special_server');
    }
};
