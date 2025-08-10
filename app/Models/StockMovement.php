<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi', 'product_id', 'ruko_id', 'jenis_transaksi',
        'jumlah', 'stok_sebelum', 'stok_sesudah', 'keterangan', 'user_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function ruko()
    {
        return $this->belongsTo(Ruko::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByJenisTransaksi($query, $jenis)
    {
        return $query->where('jenis_transaksi', $jenis);
    }
}
