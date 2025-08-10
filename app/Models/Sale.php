<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_penjualan', 'ruko_id', 'total_amount', 'total_profit',
        'status', 'tanggal_penjualan', 'user_id', 'catatan'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'tanggal_penjualan' => 'datetime',
    ];

    public function ruko()
    {
        return $this->belongsTo(Ruko::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function calculateTotals()
    {
        $this->total_amount = $this->items->sum('subtotal');
        $this->total_profit = $this->items->sum('profit');
        $this->save();
    }
}