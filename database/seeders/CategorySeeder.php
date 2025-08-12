<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama_kategori' => 'Premium', 'deskripsi' => 'Minuman beralkohol premium umumnya memiliki proses produksi yang halus, bahan-bahan berkualitas, dan keahlian khusus. Minuman premium juga memiliki ciri khas aroma, rasa, kemurnian, dan kejernihan.'],
            ['nama_kategori' => 'Beer', 'deskripsi' => 'Bir adalah minuman beralkohol yang dibuat melelui proses fermentasi bahan berpati, seperti gandum, jagung, atau beras, dengan menggunakan ragi dan hop. Proses ini menghasikan akohol dalam kadar yang bervariasi, tergantung pada jenis bir.'],
            ['nama_kategori' => 'Soju', 'deskripsi' => 'Soju adalah minuman beralkohol khas Korea yang terbuat dari beras, ubi jalar, gandum, jelai, atau tapioka. Soju memiliki warna bening dan kadar alkohol yang bervariasi.'],
            ['nama_kategori' => 'Anggur', 'deskripsi' => 'Minuman anggur, atau wine, adalah minuman beralkohol yang dihasilkan dari fermentasi buah anggur.'],
            ['nama_kategori' => 'Vodka', 'deskripsi' => 'Vodka adalah minuman beralkohol yang bening dan tidak berwarna, biasanya disuling dari bahan nabati kaya pati seperti gandum. Kadar alkohol dalam vodka umumnya berkisaran antara 35 hingga 60%.'],
            ['nama_kategori' => 'Rum', 'deskripsi' => 'Rum adalah minuman beralkohol yang berasal dari tebu yang telah memikat para peminumnya selama berabad-abad. Rum didistilasi dari jus tebu yang difermentasi atau molase, dan disimpan dalam tong kayu untuk mengembangkan citra rasanya yang kaya.'],
            ['nama_kategori' => 'Whiskey', 'deskripsi' => 'Whisky adalah minuman beralkohol yang terbuat dari biji-bijian yang difermentasi dan disuling, kemudian disimpan dalam tong kayu, biasanya kayu ek, untuk memberikan rasa dan warna khas.  '],
            ['nama_kategori' => 'Vibe', 'deskripsi' => 'Vibe adalah minuman beralkohol yang diproduksi oleh PT Khrisma Serasi Jaya. Minuman ini memiliki beberapa varian rasa, seperti back tea, exotic lychee, triple sec, coconut, rum, vodka premium, whisky, tequila, dry gin, sambuca, apricot, dan peach.'],
            ['nama_kategori' => 'P.Bundling', 'deskripsi' => 'package gabungan'],
            ['nama_kategori' => 'Food', 'deskripsi' => 'Aneka menu makanan dan cemilan'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}