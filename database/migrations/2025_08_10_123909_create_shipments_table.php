<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pengiriman')->unique();
            $table->foreignId('ruko_id')->constrained('rukos');
            $table->enum('status', ['dalam_perjalanan', 'selesai', 'dibatalkan']);
            $table->date('tanggal_pengiriman');
            $table->date('tanggal_diterima')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('pengirim_id')->constrained('users');
            $table->foreignId('penerima_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['ruko_id', 'status', 'tanggal_pengiriman']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
