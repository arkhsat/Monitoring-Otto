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
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'sumber_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('sumber_id')
                    ->nullable()
                    ->after('lokasi_id')
                    ->constrained('sumber_transaksis')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'sumber_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['sumber_id']);
                $table->dropColumn('sumber_id');
            });
        }
    }
};

