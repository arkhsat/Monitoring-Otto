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
        Schema::create('online_monitoring_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_monitoring_id')->constrained('online_monitorings')->cascadeOnDelete();
            $table->foreignId('lokasi_id')->constrained('lokasis')->cascadeOnDelete();
            $table->string('jaringan');
            $table->string('previous_status');
            $table->string('current_status');
            $table->timestamp('downtime_start')->nullable()->index();
            $table->timestamp('downtime_end')->nullable()->index();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('recovery_time')->nullable();
            $table->timestamp('recorded_at')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_monitoring_histories');
    }
};

