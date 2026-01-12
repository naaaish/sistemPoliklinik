# 🏥 PANDUAN MENJALANKAN DASHBOARD SISTEM POLIKLINIK

## 📋 Yang Sudah Dibuat

✅ Dashboard utama (publik - bisa diakses tanpa login)
✅ Halaman Artikel Kesehatan (publik)
✅ Halaman Tentang Kami (publik)
✅ Halaman Riwayat Pemeriksaan (HARUS LOGIN)
✅ Sistem Login & Logout
✅ Data sample dokter, jadwal, dan artikel

---

## 🚀 CARA MENJALANKAN

### 1️⃣ Jalankan Database Seeder

```bash
php artisan db:seed --class=DashboardSeeder
```

Ini akan membuat data sample:
- 3 Dokter
- 6 Jadwal Dokter
- 6 Artikel Kesehatan

### 2️⃣ Buat User untuk Testing Login

```bash
php artisan tinker
```

Kemudian ketik:

```php
\App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@test.com',
    'password' => bcrypt('password')
]);
```

Tekan `Ctrl+D` untuk keluar dari tinker.

### 3️⃣ Jalankan Server

```bash
php artisan serve
```

### 4️⃣ Buka di Browser

Buka: **http://localhost:8000**

---

## 🔑 Akun Login untuk Testing

**Email:** test@test.com  
**Password:** password

---

## 🧪 TESTING FITUR

### ✅ Test 1: Akses Dashboard Tanpa Login
1. Buka `http://localhost:8000`
2. ✔️ Harus bisa melihat dashboard dengan:
   - Hero section "Selamat Datang, User!"
   - Jadwal Dokter (3 dokter)
   - Artikel Kesehatan (4 artikel di home)
   - Tentang Kami section

### ✅ Test 2: Klik Menu Artikel (Tanpa Login)
1. Klik menu "Artikel Kesehatan" di navbar
2. ✔️ Harus masuk ke halaman artikel
3. ✔️ Menampilkan 6 artikel kesehatan

### ✅ Test 3: Klik Menu Tentang Kami (Tanpa Login)
1. Klik menu "Tentang Kami" di navbar
2. ✔️ Harus masuk ke halaman tentang kami
3. ✔️ Menampilkan informasi poliklinik

### ✅ Test 4: Klik Riwayat Pemeriksaan (Belum Login)
1. Klik menu "Riwayat Pemeriksaan" di navbar
2. ✔️ Harus otomatis redirect ke halaman login
3. ✔️ URL berubah ke `/login`

### ✅ Test 5: Login dan Akses Riwayat
1. Di halaman login, masukkan:
   - Email: `test@test.com`
   - Password: `password`
2. Klik "Masuk"
3. ✔️ Harus berhasil login
4. ✔️ Tombol "Login" di navbar berubah jadi "Logout"
5. Klik menu "Riwayat Pemeriksaan"
6. ✔️ Harus bisa masuk ke halaman riwayat
7. ✔️ Menampilkan "Belum Ada Riwayat" (karena belum ada data)

### ✅ Test 6: Logout
1. Setelah login, klik tombol "Logout" di navbar
2. ✔️ Harus logout dan kembali ke dashboard
3. ✔️ Tombol berubah kembali jadi "Login"
4. Coba klik "Riwayat Pemeriksaan" lagi
5. ✔️ Harus diminta login lagi

---

## 📸 Struktur Folder Gambar

Jika ingin menambahkan foto dokter atau gambar artikel, letakkan di:

```
public/
  images/
    doctors/      ← Foto dokter (contoh: dr-aulia.jpg)
    articles/     ← Gambar artikel (contoh: flu.jpg)
```

Kemudian update di database:
- Kolom `foto` di tabel `dokter`
- Kolom `gambar` di tabel `artikel`

---

## 🎨 Tampilan

- **Warna Tema:** Biru (#4a6fa5)
- **Design:** Modern, gradient, responsive
- **Layout:** Clean dan user-friendly

---

## 📝 Route Summary

| URL | Akses | Deskripsi |
|-----|-------|-----------|
| `/` | Public | Dashboard utama |
| `/tentang` | Public | Tentang kami |
| `/artikel` | Public | List artikel |
| `/login` | Public | Halaman login |
| `/riwayat` | **Protected** | Riwayat pemeriksaan (harus login) |

---

## ✨ Fitur Keamanan

- ✅ Middleware `auth` melindungi halaman riwayat
- ✅ Auto redirect ke login jika belum login
- ✅ Session management
- ✅ CSRF Protection
- ✅ Password hashing dengan bcrypt

---

## 🐛 Troubleshooting

### Error: "Class DashboardSeeder not found"
```bash
composer dump-autoload
php artisan db:seed --class=DashboardSeeder
```

### Error: Database connection
Cek file `.env` pastikan konfigurasi database sudah benar:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

Kemudian:
```bash
php artisan migrate
```

### Halaman blank/error
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi tim development.

**Happy Coding! 🚀**
