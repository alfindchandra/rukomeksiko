<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('ruko_id')->nullable()->constrained('rukos');
            $table->enum('jenis_transaksi', ['masuk', 'keluar', 'transfer', 'penjualan']);
            $table->integer('jumlah');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['product_id', 'jenis_transaksi', 'created_at']);
            $table->index('kode_transaksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
