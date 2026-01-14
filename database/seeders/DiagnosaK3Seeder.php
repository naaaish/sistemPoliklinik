<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema; // Tambahkan ini jika truncate error foreign key

class DiagnosaK3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- TAMBAHAN PENTING ---
        // Kosongkan tabel dulu sebelum insert agar tidak duplicate entry
        // Kita disable foreign key check sebentar jaga-jaga kalau tabel ini sudah dipakai relasi
        Schema::disableForeignKeyConstraints();
        DB::table('diagnosa_k3')->truncate();
        Schema::enableForeignKeyConstraints();
        // ------------------------

        $now = Carbon::now();

        $data = [
            // 1. SALURAN PERNAFASAN
            [
                'id_nb' => '1.1',
                'kategori_penyakit' => 'SALURAN PERNAFASAN',
                'nama_penyakit' => 'Infeksi saluran pernafasan bagian atas termasuk influenza, Tonsilitis, Pharingitis, Laringitis, Sinusitis, Rhinitis dan lain-lain.',
            ],
            [
                'id_nb' => '1.2',
                'kategori_penyakit' => 'SALURAN PERNAFASAN',
                'nama_penyakit' => 'Infeksi saluran pernafasan lain termasuk Bronchitis, Pleuritis, Pneumonia, Asma dan lain-lain.',
            ],
            [
                'id_nb' => '1.3',
                'kategori_penyakit' => 'SALURAN PERNAFASAN',
                'nama_penyakit' => 'Tuberkulosa (TB)',
            ],
            [
                'id_nb' => '1.4',
                'kategori_penyakit' => 'SALURAN PERNAFASAN',
                'nama_penyakit' => 'Tonsilo Faringitis',
            ],

            // 2. SALURAN PENCERNAAN
            [
                'id_nb' => '2.1',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Gastritis/Tukak Lambung',
            ],
            [
                'id_nb' => '2.2',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Kolera',
            ],
            [
                'id_nb' => '2.3',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Diare, Dysentri',
            ],
            [
                'id_nb' => '2.4',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Typus Abdominalis, paratyphus',
            ],
            [
                'id_nb' => '2.5',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Radang hati',
            ],
            [
                'id_nb' => '2.6',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Dispepsi',
            ],
            [
                'id_nb' => '2.7',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Hycoup',
            ],
            [
                'id_nb' => '2.8',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Ascariasis',
            ],
            [
                'id_nb' => '2.9',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Kolik Abdomen',
            ],
            [
                'id_nb' => '2.10',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Konstipasi',
            ],
            [
                'id_nb' => '2.11',
                'kategori_penyakit' => 'SALURAN PENCERNAAN',
                'nama_penyakit' => 'Lainnya sebutkan ..........',
            ],

            // 3. GINJAL DAN SALURAN KEMIH
            [
                'id_nb' => '3.1',
                'kategori_penyakit' => 'GINJAL DAN SALURAN KEMIH',
                'nama_penyakit' => 'Radang ginjal dan saluran kencing',
            ],
            [
                'id_nb' => '3.2',
                'kategori_penyakit' => 'GINJAL DAN SALURAN KEMIH',
                'nama_penyakit' => 'Batu ginjal dan saluran kencing',
            ],
            [
                'id_nb' => '3.3',
                'kategori_penyakit' => 'GINJAL DAN SALURAN KEMIH',
                'nama_penyakit' => 'Lainnya sebutkan ..........',
            ],

            // 4. PENYAKIT JANTUNG DAN TEKANAN DARAH
            [
                'id_nb' => '4.1',
                'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH',
                'nama_penyakit' => 'Hypertensi',
            ],
            [
                'id_nb' => '4.2',
                'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH',
                'nama_penyakit' => 'Hypotensi',
            ],
            [
                'id_nb' => '4.3',
                'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH',
                'nama_penyakit' => 'Penyakit Jantung',
            ],
            [
                'id_nb' => '4.4',
                'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH',
                'nama_penyakit' => 'Lainnya sebutkan ...................',
            ],

            // 5. KELAINAN PEMBULUH DARAH
            [
                'id_nb' => '5.1',
                'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH',
                'nama_penyakit' => 'Wasir',
            ],
            [
                'id_nb' => '5.2',
                'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH',
                'nama_penyakit' => 'Varises',
            ],
            [
                'id_nb' => '5.3',
                'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH',
                'nama_penyakit' => 'Phlebitis',
            ],
            [
                'id_nb' => '5.4',
                'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH',
                'nama_penyakit' => 'Demam Berdarah Dengue',
            ],
            [
                'id_nb' => '5.5',
                'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH',
                'nama_penyakit' => 'Lainnya sebutkan ...................',
            ],

            // 6. KELAINAN DARAH
            [
                'id_nb' => '6.1',
                'kategori_penyakit' => 'KELAINAN DARAH',
                'nama_penyakit' => 'Anemia',
            ],
            [
                'id_nb' => '6.2',
                'kategori_penyakit' => 'KELAINAN DARAH',
                'nama_penyakit' => 'Kelainan darah lainnya sebutkan.......',
            ],

            // 7. PENYAKIT OTOT DAN KERANGKA
            [
                'id_nb' => '7.1',
                'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA',
                'nama_penyakit' => 'Myalgia, athralgia',
            ],
            [
                'id_nb' => '7.2',
                'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA',
                'nama_penyakit' => 'Arthitis, Rhematoid termasuk Gout',
            ],
            [
                'id_nb' => '7.3',
                'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA',
                'nama_penyakit' => 'Hernia Nukleus Pulposus',
            ],
            [
                'id_nb' => '7.4',
                'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA',
                'nama_penyakit' => 'Osteoarthitis',
            ],
        ];

        foreach ($data as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('diagnosa_k3')->insert($data);
    }
}