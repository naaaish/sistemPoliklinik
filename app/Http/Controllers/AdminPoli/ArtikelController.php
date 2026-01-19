<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class ArtikelController extends Controller
{
    private function nextIdArtikel(): string
    {
        $lastId = DB::table('artikel')
            ->where('id_artikel', 'like', 'ART-%')
            ->orderByRaw("CAST(SUBSTRING(id_artikel, 5) AS UNSIGNED) DESC")
            ->value('id_artikel');

        $nextNumber = 1;
        if ($lastId) {
            $nextNumber = (int) substr($lastId, 4) + 1; // "ART-" = 4 char
        }

        return 'ART-' . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function defaultCover(): string
    {
        // pastikan file ini ada: public/artikel-cover/default.png
        return 'artikel-cover/default.png';
    }

    private function saveCoverFile(?\Illuminate\Http\UploadedFile $file): string
    {
        if (!$file) return $this->defaultCover();

        $dir = public_path('artikel-cover');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 'cover_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $ext;

        $file->move($dir, $name);

        return 'artikel-cover/' . $name;
    }

    private function deleteCoverIfNeeded(?string $coverPath): void
    {
        if (!$coverPath) return;
        if ($coverPath === $this->defaultCover()) return;

        $full = public_path($coverPath);
        if (is_file($full)) {
            @unlink($full);
        }
    }

    public function index(Request $request)
    {
        $query = DB::table('artikel');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('judul_artikel', 'like', '%' . $q . '%');
        }

        $artikel = $query
            ->select('id_artikel', 'judul_artikel', 'tanggal', 'cover_path', 'updated_at')
            ->orderByDesc('tanggal')
            ->orderByDesc('updated_at')
            ->get();

        return view('adminpoli.artikel.index', compact('artikel'));
    }

    public function create()
    {
        return view('adminpoli.artikel.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_artikel' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'isi_artikel'   => 'required|string',
            'cover'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $id = $this->nextIdArtikel();
        $coverPath = $this->saveCoverFile($request->file('cover'));

        DB::table('artikel')->insert([
            'id_artikel'    => $id,
            'judul_artikel' => $request->judul_artikel,
            'tanggal'       => $request->tanggal,
            'cover_path'    => $coverPath,
            'isi_artikel'   => $request->isi_artikel,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('adminpoli.artikel.edit', $id)
            ->with('success', 'Artikel berhasil dibuat. Silakan lanjut edit.');
    }

    public function edit($id)
    {
        $artikel = DB::table('artikel')->where('id_artikel', $id)->first();
        if (!$artikel) {
            return redirect()->route('adminpoli.artikel.index')->with('error', 'Artikel tidak ditemukan.');
        }

        return view('adminpoli.artikel.edit', compact('artikel'));
    }

    public function update(Request $request, $id)
    {
        $artikel = DB::table('artikel')->where('id_artikel', $id)->first();
        if (!$artikel) {
            return redirect()->route('adminpoli.artikel.index')->with('error', 'Artikel tidak ditemukan.');
        }

        $request->validate([
            'judul_artikel' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'isi_artikel'   => 'required|string',
            'cover'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $coverPath = $artikel->cover_path ?: $this->defaultCover();

        // kalau upload cover baru → replace
        if ($request->hasFile('cover')) {
            $this->deleteCoverIfNeeded($coverPath);
            $coverPath = $this->saveCoverFile($request->file('cover'));
        }

        DB::table('artikel')
            ->where('id_artikel', $id)
            ->update([
                'judul_artikel' => $request->judul_artikel,
                'tanggal'       => $request->tanggal,
                'cover_path'    => $coverPath,
                'isi_artikel'   => $request->isi_artikel,
                'updated_at'    => now(),
            ]);

        return redirect()->route('adminpoli.artikel.edit', $id)
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $artikel = DB::table('artikel')->where('id_artikel', $id)->first();
        if ($artikel) {
            $this->deleteCoverIfNeeded($artikel->cover_path);
            DB::table('artikel')->where('id_artikel', $id)->delete();
        }

        return redirect()->route('adminpoli.artikel.index')->with('success', 'Artikel berhasil dihapus.');
    }

    /**
     * Upload dari PDF/Word → bikin draft → redirect ke edit.
     * Catatan: parsing isi file aku buat "best effort".
     * - docx (zip) biasanya bisa diekstrak dari word/document.xml
     * - pdf parsing butuh package; kalau ga ada, isi_artikel diisi placeholder.
     */
    public function importDoc(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension() ?: '');

        $judul = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $judul = trim($judul) !== '' ? $judul : 'Artikel Baru';

        $isi = '';

        // ======= DOCX parsing sederhana (tanpa library) =======
        if ($ext === 'docx') {
            try {
                $tmp = $file->getRealPath();
                $zip = new \ZipArchive();
                if ($zip->open($tmp) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();

                    if ($xml) {
                        // hapus tag xml jadi text
                        $xml = str_replace(['</w:p>', '</w:tr>'], "\n", $xml);
                        $isi = strip_tags($xml);
                        $isi = preg_replace("/\n{3,}/", "\n\n", $isi);
                        $isi = trim($isi);
                    }
                }
            } catch (\Throwable $e) {
                // fallback di bawah
            }
        }

        // ======= PDF parsing (butuh package kalau ada) =======
        // if ($ext === 'pdf') {
        //     // Best effort: kalau ada spatie/pdf-to-text
        //     try {
        //         if (class_exists(\Spatie\PdfToText\Pdf::class)) {
        //             $isi = \Spatie\PdfToText\Pdf::getText($file->getRealPath());
        //             $isi = trim((string)$isi);
        //         }
        //     } catch (\Throwable $e) {
        //         // fallback di bawah
        //     }
        // }

        // ======= DOC (legacy) → fallback =======
        // .doc lama susah tanpa library. Jadi fallback aja.

        if (!$isi) {
            $isi = "## Draft dari file: {$file->getClientOriginalName()}\n\n"
                 . "(Isi belum berhasil diekstrak otomatis. Kamu bisa paste isi artikel di sini.)";
        }

        $id = $this->nextIdArtikel();

        DB::table('artikel')->insert([
            'id_artikel'    => $id,
            'judul_artikel' => $judul,
            'tanggal'       => date('Y-m-d'),
            'cover_path'    => $this->defaultCover(),
            'isi_artikel'   => $isi,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('adminpoli.artikel.edit', $id)
            ->with('success', 'Draft artikel berhasil dibuat dari file. Silakan cek & rapikan di halaman edit.');
    }
}
