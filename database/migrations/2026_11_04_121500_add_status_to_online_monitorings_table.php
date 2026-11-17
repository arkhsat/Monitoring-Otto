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
        Schema::table('online_monitorings', function (Blueprint $table) {
            if (!Schema::hasColumn('online_monitorings', 'status')) {
                $table->string('status')->default('Offline')->after('ip_address')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_monitorings', function (Blueprint $table) {
            if (Schema::hasColumn('online_monitorings', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

