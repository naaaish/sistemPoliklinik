<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DiagnosaK3Seeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('diagnosa_k3')->truncate();
        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        // =========================
        // 1) DATA KATEGORI (PARENT)
        // =========================
        $kategori = [
            ['id_nb' => '1', 'kategori_penyakit' => 'SALURAN PERNAFASAN'],
            ['id_nb' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN'],
            ['id_nb' => '3', 'kategori_penyakit' => 'GINJAL DAN SALURAN KEMIH'],
            ['id_nb' => '4', 'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH'],
            ['id_nb' => '5', 'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH'],
            ['id_nb' => '6', 'kategori_penyakit' => 'KELAINAN DARAH'],
            ['id_nb' => '7', 'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA'],
        ];

        $kategoriRows = [];
        foreach ($kategori as $k) {
            $kategoriRows[] = [
                'id_nb'            => $k['id_nb'],
                'tipe'             => 'kategori',
                'parent_id'        => null,
                // biar tidak kosong, kita isi nama_penyakit sama dengan judul kategorinya
                'nama_penyakit'    => $k['kategori_penyakit'],
                'kategori_penyakit'=> $k['kategori_penyakit'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        DB::table('diagnosa_k3')->insert($kategoriRows);

        // =========================
        // 2) DATA PENYAKIT (CHILD)
        // =========================
        $penyakit = [
            // 1. SALURAN PERNAFASAN
            ['id_nb' => '1.1', 'parent_id' => '1', 'kategori_penyakit' => 'SALURAN PERNAFASAN', 'nama_penyakit' => 'Infeksi saluran pernafasan bagian atas termasuk influenza, Tonsilitis, Pharingitis, Laringitis, Sinusitis, Rhinitis dan lain-lain.'],
            ['id_nb' => '1.2', 'parent_id' => '1', 'kategori_penyakit' => 'SALURAN PERNAFASAN', 'nama_penyakit' => 'Infeksi saluran pernafasan lain termasuk Bronchitis, Pleuritis, Pneumonia, Asma dan lain-lain.'],
            ['id_nb' => '1.3', 'parent_id' => '1', 'kategori_penyakit' => 'SALURAN PERNAFASAN', 'nama_penyakit' => 'Tuberkulosa (TB)'],
            ['id_nb' => '1.4', 'parent_id' => '1', 'kategori_penyakit' => 'SALURAN PERNAFASAN', 'nama_penyakit' => 'Tonsilo Faringitis'],

            // 2. SALURAN PENCERNAAN
            ['id_nb' => '2.1', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Gastritis/Tukak Lambung'],
            ['id_nb' => '2.2', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Kolera'],
            ['id_nb' => '2.3', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Diare, Dysentri'],
            ['id_nb' => '2.4', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Typus Abdominalis, paratyphus'],
            ['id_nb' => '2.5', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Radang hati'],
            ['id_nb' => '2.6', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Dispepsi'],
            ['id_nb' => '2.7', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Hycoup'],
            ['id_nb' => '2.8', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Ascariasis'],
            ['id_nb' => '2.9', 'parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Kolik Abdomen'],
            ['id_nb' => '2.10','parent_id' => '2', 'kategori_penyakit' => 'SALURAN PENCERNAAN', 'nama_penyakit' => 'Konstipasi'],

            // 3. GINJAL DAN SALURAN KEMIH
            ['id_nb' => '3.1', 'parent_id' => '3', 'kategori_penyakit' => 'GINJAL DAN SALURAN KEMIH', 'nama_penyakit' => 'Radang ginjal dan saluran kencing'],
            ['id_nb' => '3.2', 'parent_id' => '3', 'kategori_penyakit' => 'GINJAL DAN SALURAN KEMIH', 'nama_penyakit' => 'Batu ginjal dan saluran kencing'],

            // 4. PENYAKIT JANTUNG DAN TEKANAN DARAH
            ['id_nb' => '4.1', 'parent_id' => '4', 'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH', 'nama_penyakit' => 'Hypertensi'],
            ['id_nb' => '4.2', 'parent_id' => '4', 'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH', 'nama_penyakit' => 'Hypotensi'],
            ['id_nb' => '4.3', 'parent_id' => '4', 'kategori_penyakit' => 'PENYAKIT JANTUNG DAN TEKANAN DARAH', 'nama_penyakit' => 'Penyakit Jantung'],

            // 5. KELAINAN PEMBULUH DARAH
            ['id_nb' => '5.1', 'parent_id' => '5', 'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH', 'nama_penyakit' => 'Wasir'],
            ['id_nb' => '5.2', 'parent_id' => '5', 'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH', 'nama_penyakit' => 'Varises'],
            ['id_nb' => '5.3', 'parent_id' => '5', 'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH', 'nama_penyakit' => 'Phlebitis'],
            ['id_nb' => '5.4', 'parent_id' => '5', 'kategori_penyakit' => 'KELAINAN PEMBULUH DARAH', 'nama_penyakit' => 'Demam Berdarah Dengue'],

            // 6. KELAINAN DARAH
            ['id_nb' => '6.1', 'parent_id' => '6', 'kategori_penyakit' => 'KELAINAN DARAH', 'nama_penyakit' => 'Anemia'],

            // 7. PENYAKIT OTOT DAN KERANGKA
            ['id_nb' => '7.1', 'parent_id' => '7', 'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA', 'nama_penyakit' => 'Myalgia, athralgia'],
            ['id_nb' => '7.2', 'parent_id' => '7', 'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA', 'nama_penyakit' => 'Arthitis, Rhematoid termasuk Gout'],
            ['id_nb' => '7.3', 'parent_id' => '7', 'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA', 'nama_penyakit' => 'Hernia Nukleus Pulposus'],
            ['id_nb' => '7.4', 'parent_id' => '7', 'kategori_penyakit' => 'PENYAKIT OTOT DAN KERANGKA', 'nama_penyakit' => 'Osteoarthitis'],
        ];

        $penyakitRows = [];
        foreach ($penyakit as $p) {
            $penyakitRows[] = [
                'id_nb'             => $p['id_nb'],
                'tipe'              => 'penyakit',
                'parent_id'         => $p['parent_id'],
                'nama_penyakit'     => $p['nama_penyakit'],
                'kategori_penyakit' => $p['kategori_penyakit'],
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        DB::table('diagnosa_k3')->insert($penyakitRows);
    }
}
