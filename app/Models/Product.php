<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang', 'nama_barang', 'deskripsi', 'category_id', 
        'harga_beli', 'harga_jual', 'stok_minimum', 'satuan', 'is_active'
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function shipmentItems()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStokGudangPusatAttribute()
    {
        return $this->inventories()->whereNull('ruko_id')->first()->stok_tersedia ?? 0;
    }

    public function getStokRuko($rukoId)
    {
        return $this->inventories()->where('ruko_id', $rukoId)->first()->stok_tersedia ?? 0;
    }
}