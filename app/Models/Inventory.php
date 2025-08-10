<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'ruko_id', 'stok_tersedia', 'stok_reserved'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function ruko()
    {
        return $this->belongsTo(Ruko::class);
    }

    public function scopeGudangPusat($query)
    {
        return $query->whereNull('ruko_id');
    }

    public function scopeRuko($query, $rukoId)
    {
        return $query->where('ruko_id', $rukoId);
    }

    public function getStatusStokAttribute()
    {
        if ($this->stok_tersedia <= $this->product->stok_minimum) {
            return 'menipis';
        }
        return 'aman';
    }
}
