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
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lokasi_id')->nullable()->constrained('lokasis')->nullOnDelete();
                $table->string('token_source', 100)->nullable()->index();
                $table->string('transactionid', 100)->index();
                $table->dateTime('timein')->nullable();
                $table->string('nokartubank', 100)->nullable();
                $table->string('posinid', 50)->nullable();
                $table->text('image_plate_in')->nullable();
                $table->string('vehicleid', 50)->nullable();
                $table->string('nopolisi', 50)->nullable();
                $table->dateTime('dateout')->nullable()->index();
                $table->string('status', 50)->default('complete');
                $table->integer('cost')->default(0);
                $table->string('paymentby', 50)->nullable();
                $table->string('posoutid', 50)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

