<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = [
            [
                'name' => 'Gramedia Pustaka Utama',
                'type' => 'publisher',
                'contact_person' => 'Rina Sasmita (Sales Area)',
                'email' => 'sales@gramediapustaka.id',
                'phone' => '021-53650110',
                'region' => 'Jakarta Pusat',
                'address' => 'Gedung Kompas Gramedia Blok 1 Lt. 5, Jl. Palmerah Barat No. 29-37, Gelora, Tanah Abang, Jakarta Pusat 10270',
                'notes' => 'Penerbit utama buku fiksi, sastra, dan nonfiksi populer. Diskon pengadaan perpustakaan 25%.',
            ],
            [
                'name' => 'Mizan Pustaka',
                'type' => 'publisher',
                'contact_person' => 'Ahmad Fadhil (Narahubung Kerjasama)',
                'email' => 'kerjasama@mizan.com',
                'phone' => '022-7834310',
                'region' => 'Bandung, Jawa Barat',
                'address' => 'Jl. Cinambo No. 135, Cisaranten Wetan, Cinambo, Kota Bandung, Jawa Barat 40294',
                'notes' => 'Penerbit buku keislaman, sastra, pengembangan diri, dan literatur anak.',
            ],
            [
                'name' => 'Penerbit Erlangga',
                'type' => 'publisher',
                'contact_person' => 'Bambang Supriyadi (Sales Rep)',
                'email' => 'bambang.sales@erlangga.co.id',
                'phone' => '021-8717006',
                'region' => 'Jakarta Timur',
                'address' => 'Jl. H. Baping Raya No. 100, Ciracas, Jakarta Timur 13740',
                'notes' => 'Penerbit buku referensi akademik, sains, ensiklopedia, dan buku pelajaran.',
            ],
            [
                'name' => 'Bentang Pustaka',
                'type' => 'publisher',
                'contact_person' => 'Siti Nurhaliza (Account Representative)',
                'email' => 'pemasaran@bentangpustaka.com',
                'phone' => '0274-885431',
                'region' => 'DI Yogyakarta',
                'address' => 'Jl. Palagan Tentara Pelajar KM 9.3, Karang Moko, Sariharjo, Ngaglik, Sleman, DI Yogyakarta 55581',
                'notes' => 'Fokus karya sastra dan novel populer Indonesia.',
            ],
            [
                'name' => 'CV Agromedia Mandiri Distribusi',
                'type' => 'distributor',
                'contact_person' => 'Hendra Setiawan (Koordinator Distribusi)',
                'email' => 'distribusi@agromedia.group',
                'phone' => '021-78883030',
                'region' => 'Jakarta Selatan',
                'address' => 'Jl. H. Montong No. 57, Ciganjur, Jagakarsa, Jakarta Selatan 12630',
                'notes' => 'Distributor buku skala nasional melayani pengadaan buku perpustakaan sekolah dan kampus.',
            ],
            [
                'name' => 'CV Niaga Pustaka Mandiri',
                'type' => 'distributor',
                'contact_person' => 'Dwi Cahyono',
                'email' => 'niaga.pustaka@gmail.com',
                'phone' => '081234567890',
                'region' => 'Surabaya, Jawa Timur',
                'address' => 'Jl. Rungkut Madya No. 88, Gunung Anyar, Kota Surabaya, Jawa Timur 60295',
                'notes' => 'Pemasok dan rekanan pengadaan bahan pustaka wilayah Jawa Timur dan Indonesia Timur.',
            ],
        ];

        foreach ($publishers as $data) {
            Publisher::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
