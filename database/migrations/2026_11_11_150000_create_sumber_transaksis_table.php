<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sumber_transaksis')) {
            Schema::create('sumber_transaksis', function (Blueprint $table) {
                $table->id();
                $table->string('kode_sumber', 50)->unique();
                $table->string('nama_sumber', 100);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });

            // Insert initial default sources
            DB::table('sumber_transaksis')->insert([
                [
                    'id'          => 1,
                    'kode_sumber' => 'admin_report',
                    'nama_sumber' => 'Report Lokasi & Admin Otto',
                    'keterangan'  => 'Data transaksi riil dari pos parkir lokasi dan admin otto',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'id'          => 2,
                    'kode_sumber' => 'settlement_mdd',
                    'nama_sumber' => 'Settlement MDD',
                    'keterangan'  => 'Data settlement transaksi dari gateway MDD',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sumber_transaksis');
    }
};

