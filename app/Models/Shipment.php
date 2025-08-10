<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_pengiriman', 'ruko_id', 'status', 'tanggal_pengiriman',
        'tanggal_diterima', 'catatan', 'pengirim_id', 'penerima_id'
    ];

    protected $casts = [
        'tanggal_pengiriman' => 'date',
        'tanggal_diterima' => 'date',
    ];

    public function ruko()
    {
        return $this->belongsTo(Ruko::class);
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'dalam_perjalanan');
    }
}
