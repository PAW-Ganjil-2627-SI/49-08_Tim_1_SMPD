<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

/**
 * Data referensi sederhana (kategori, penerbit, pengarang) agar CRUD buku dapat dicoba
 * pada database yang masih kosong.
 *
 * Tidak dipanggil oleh DatabaseSeeder. Jalankan manual:
 *     php artisan db:seed --class=BookReferenceSeeder
 *
 * Aman dijalankan berulang kali (firstOrCreate: tidak membuat data ganda).
 */
class BookReferenceSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('BookReferenceSeeder dilewati di environment production.');

            return;
        }

        foreach (['Fiksi', 'Nonfiksi', 'Sains', 'Teknologi', 'Sejarah'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }

        foreach (['Gramedia Pustaka Utama', 'Mizan Pustaka', 'Erlangga', 'Balai Pustaka', 'Republika Penerbit'] as $name) {
            Publisher::firstOrCreate(['name' => $name, 'type' => 'publisher']);
        }

        foreach (['Penulis Contoh A', 'Penulis Contoh B', 'Penulis Contoh C', 'Penulis Contoh D'] as $name) {
            Author::firstOrCreate(['name' => $name]);
        }
    }
}
