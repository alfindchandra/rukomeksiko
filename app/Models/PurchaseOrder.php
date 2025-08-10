<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_po', 'supplier_id', 'status', 'total_amount',
        'tanggal_order', 'tanggal_terima', 'catatan', 'user_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tanggal_order' => 'date',
        'tanggal_terima' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function calculateTotal()
    {
        $this->total_amount = $this->items->sum('subtotal');
        $this->save();
    }
}