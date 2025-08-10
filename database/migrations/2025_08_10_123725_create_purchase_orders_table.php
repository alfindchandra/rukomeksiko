<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_po')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->enum('status', ['draft', 'dipesan', 'diterima', 'dibatalkan']);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->date('tanggal_order');
            $table->date('tanggal_terima')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['status', 'tanggal_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
