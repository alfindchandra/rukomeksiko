<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan')->unique();
            $table->foreignId('ruko_id')->constrained('rukos');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('total_profit', 15, 2);
            $table->enum('status', ['selesai', 'dibatalkan']);
            $table->timestamp('tanggal_penjualan');
            $table->foreignId('user_id')->constrained('users');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['ruko_id', 'tanggal_penjualan']);
            $table->index(['status', 'tanggal_penjualan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
