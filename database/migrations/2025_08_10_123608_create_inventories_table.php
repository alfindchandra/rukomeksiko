<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('ruko_id')->nullable()->constrained('rukos');
            $table->integer('stok_tersedia')->default(0);
            $table->integer('stok_reserved')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'ruko_id']);
            $table->index(['product_id', 'stok_tersedia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};