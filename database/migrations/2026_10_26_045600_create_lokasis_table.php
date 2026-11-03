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
        if (!Schema::hasTable('lokasis')) {
            Schema::create('lokasis', function (Blueprint $table) {
                $table->id();
                $table->string('uuid_lokasi')->nullable();
                $table->string('nama_lokasi');
                $table->text('keterangan_alamat')->nullable();
                $table->string('api_lokasi')->nullable();
                $table->integer('batas_waktu_henti')->default(60)->comment('Batas waktu henti / downtime limit threshold in seconds');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasis');
    }
};
