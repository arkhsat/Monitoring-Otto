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
        Schema::table('lokasis', function (Blueprint $table) {
            if (!Schema::hasColumn('lokasis', 'uuid_lokasi')) {
                $table->string('uuid_lokasi')->nullable()->after('id');
            }
            if (!Schema::hasColumn('lokasis', 'api_lokasi')) {
                $table->string('api_lokasi')->nullable()->after('keterangan_alamat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokasis', function (Blueprint $table) {
            if (Schema::hasColumn('lokasis', 'uuid_lokasi')) {
                $table->dropColumn('uuid_lokasi');
            }
            if (Schema::hasColumn('lokasis', 'api_lokasi')) {
                $table->dropColumn('api_lokasi');
            }
        });
    }
};

