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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name');
            $table->string('user', 1024)->nullable();
            $table->string('host', 1024);
            $table->unsignedInteger('port')->default(22)->nullable();
            $table->string('password', 1024)->nullable();
            $table->string('private_key_path', 1024)->nullable();
            $table->unsignedInteger('is_connected')->nullable();
            $table->dateTime('last_connection_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
