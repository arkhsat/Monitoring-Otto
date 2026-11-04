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
        Schema::create('online_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_id')->unique()->constrained('lokasis')->cascadeOnDelete();
            $table->string('jaringan');
            $table->string('ip_address')->nullable();
            $table->timestamp('last_update')->nullable()->index();
            $table->timestamp('first_monitored_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_monitorings');
    }
};
