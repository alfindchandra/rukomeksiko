<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'kode_barang' => 'PRE001',
                'nama_barang' => 'Jack Daniels',
                'deskripsi' => 'Minuman beralkohol premium 700 ml 43%',
                'category_id' => 1,
                'harga_beli' => 780000,
                'harga_jual' => 800000,
                'stok_minimum' => 3,
                'satuan' => 'Btl'
            ],
            [
                'kode_barang' => 'PRE002',
                'nama_barang' => 'Magnolia',
                'deskripsi' => 'Minuman beralkohol premium 750ml 40%',
                'category_id' => 1,
                'harga_beli' => 380000,
                'harga_jual' => 400000,
                'stok_minimum' => 3,
                'satuan' => 'Btl'
            ],
            [
                'kode_barang' => 'BEER001',
                'nama_barang' => 'Bintang Beer',
                'deskripsi' => ' 620ml 4,7%',
                'category_id' => 2,
                'harga_beli' => 50000,
                'harga_jual' => 55000,
                'stok_minimum' => 10,
                'satuan' => 'Btl'
            ],
            [
                'kode_barang' => 'BEER002',
                'nama_barang' => 'Bintang Anggur Merah',
                'deskripsi' => '620ml 4,9%',
                'category_id' => 2,
                'harga_beli' => 50000,
                'harga_jual' => 60000,
                'stok_minimum' => 10,
                'satuan' => 'pcs'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}