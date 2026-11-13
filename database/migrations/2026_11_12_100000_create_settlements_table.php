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
        if (!Schema::hasTable('settlements')) {
            Schema::create('settlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lokasi_id')->constrained('lokasis')->cascadeOnDelete();
                $table->date('tanggal_settlement')->index();
                $table->string('file_txt_name')->nullable();
                $table->string('file_txt_path', 500)->nullable();
                $table->longText('file_txt_content')->nullable();
                $table->string('file_ok_name')->nullable();
                $table->string('file_ok_path', 500)->nullable();
                $table->longText('file_ok_content')->nullable();
                $table->timestamps();

                $table->unique(['lokasi_id', 'tanggal_settlement'], 'uniq_settlement_lokasi_tanggal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};

