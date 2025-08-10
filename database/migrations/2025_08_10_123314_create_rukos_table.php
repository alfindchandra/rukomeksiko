<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rukos', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruko')->unique();
            $table->string('nama_ruko');
            $table->text('alamat');
            $table->string('kota');
            $table->string('nomor_telepon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'kota']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rukos');
    }
};