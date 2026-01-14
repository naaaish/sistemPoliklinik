<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailResepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data ID Resep yang ada
        $list_resep = DB::table('resep')->pluck('id_resep')->toArray();
        
        // 2. Ambil data Obat lengkap (ID dan Harga) untuk perhitungan subtotal
        $list_obat = DB::table('obat')->select('id_obat', 'harga')->get();

        // Validasi agar tidak error
        if (empty($list_resep) || $list_obat->isEmpty()) {
            $this->command->warn("Data Resep atau Obat kosong. Harap seed tabel tersebut dahulu.");
            return;
        }

        $data_detail = [];

        // 3. Loop setiap Resep untuk diberikan obat
        foreach ($list_resep as $id_resep) {
            
            // Tentukan berapa jenis obat dalam 1 resep (misal: 1 s/d 3 jenis)
            $jumlah_jenis_obat = rand(1, 3);
            
            // Ambil obat acak sejumlah $jumlah_jenis_obat
            $obat_terpilih = $list_obat->random($jumlah_jenis_obat);

            foreach ($obat_terpilih as $obat) {
                
                // Tentukan jumlah beli (qty), misal 1 - 5
                $qty = rand(1, 5);

                // Hitung subtotal (Qty * Harga asli dari database)
                $subtotal = $qty * $obat->harga;

                $data_detail[] = [
                    'jumlah'   => $qty,
                    'satuan'   => $this->getSatuanRandom(), // Fungsi helper di bawah
                    'subtotal' => $subtotal,
                    'id_obat'  => $obat->id_obat,
                    'id_resep' => $id_resep,
                    // Tidak pakai created_at/updated_at karena di migration Anda tidak ada timestamps()
                ];
            }
        }

        // 4. Masukkan ke database
        // Menggunakan chunk agar insert tidak terlalu berat jika data banyak
        foreach (array_chunk($data_detail, 50) as $chunk) {
            DB::table('detail_resep')->insert($chunk);
        }

        // OPTIONAL: Update total_tagihan di tabel resep agar sinkron dengan detail
        $this->updateTotalTagihanResep();
    }

    /**
     * Helper untuk mendapatkan satuan acak
     */
    private function getSatuanRandom()
    {
        $satuan = ['Strip', 'Tablet', 'Botol', 'Kapsul', 'Pcs'];
        return $satuan[array_rand($satuan)];
    }

    /**
     * Helper Optional: Menghitung ulang total di tabel parent (Resep)
     * Agar data total_tagihan sesuai dengan jumlah detail obatnya.
     */
    private function updateTotalTagihanResep()
    {
        $reseps = DB::table('resep')->get();
        foreach($reseps as $resep) {
            $total_baru = DB::table('detail_resep')
                            ->where('id_resep', $resep->id_resep)
                            ->sum('subtotal');
            
            DB::table('resep')
                ->where('id_resep', $resep->id_resep)
                ->update(['total_tagihan' => $total_baru]);
        }
    }
}