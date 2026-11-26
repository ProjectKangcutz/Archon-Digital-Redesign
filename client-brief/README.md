# ArchonDigital Client Brief (`brief.archondigital.web.id`)

Aplikasi independen penerimaan dan pengelolaan kebutuhan calon klien pembuatan website ArchonDigital. Dibangun menggunakan **PHP native (PDO)**, **MySQL**, **HTML5**, **CSS3**, **Bootstrap 5**, dan **Vanilla JavaScript** untuk dijalankan di shared hosting **DirectAdmin**.

---

## 1. Persyaratan Minimum & Verifikasi Server Hosting

Karena lingkungan shared hosting DirectAdmin belum dapat diperiksa langsung dari lingkungan pengembangan, pastikan spesifikasi berikut tersedia pada akun hosting Anda sebelum instalasi:

- **PHP**: Minimum **PHP 8.1** (disarankan 8.2 / 8.3) dengan ekstensi aktif:
  - `pdo_mysql`
  - `mbstring`
  - `json`
  - `session`
  - `openssl`
- **Database**: **MySQL 8.0+** atau **MariaDB 10.4+** (mendukung `utf8mb4` dan engine `InnoDB`).
- **Web Server**: Apache / LiteSpeed dengan dukungan `.htaccess` (`mod_rewrite` dan `mod_headers`).

### Cara Memverifikasi Versi PHP & MySQL di DirectAdmin
1. Masuk ke panel **DirectAdmin**.
2. Buka menu **Extra Features -> Select PHP Version** atau **Account Manager -> PHP Settings** untuk memastikan PHP `8.1+` dan ekstensi `pdo_mysql` aktif.
3. Buka **System Info & Files -> Server Information** (atau buka **phpMyAdmin**) untuk melihat versi MySQL/MariaDB server.
4. Jika memiliki akses SSH, jalankan:
   ```bash
   php -v
   php -m | grep -E 'pdo_mysql|mbstring|json|session'
   mysql --version
   ```

---

## 2. Struktur Proyek

```text
client-brief/
├── .htaccess               # Proteksi folder internal jika root domain belum diarahkan ke public/
├── .env.example            # Template konfigurasi environment (jangan isi credential di sini)
├── README.md               # Dokumentasi lengkap deployment & operasional
├── bin/
│   └── create-admin.php    # Skrip CLI untuk membuat akun admin pertama secara aman
├── database/
│   └── schema.sql          # Skema tabel MySQL (admins, client_briefs, rate_limits)
├── app/
│   ├── bootstrap.php       # Loader .env, error logging aman, security headers, sesi HttpOnly
│   ├── config/
│   │   └── config.php
│   ├── database/
│   │   └── Connection.php  # Koneksi PDO dengan prepared statements
│   ├── middleware/
│   │   ├── AuthMiddleware.php
│   │   └── CsrfMiddleware.php
│   ├── services/
│   │   ├── AuthService.php
│   │   ├── BriefService.php
│   │   ├── RateLimiter.php
│   │   ├── SecurityService.php
│   │   └── ValidationService.php
│   ├── controllers/
│   │   ├── AdminController.php
│   │   └── PublicBriefController.php
│   └── views/
│       ├── partials/
│       ├── public/
│       └── admin/
├── public/                 # Document Root subdomain brief.archondigital.web.id
│   ├── .htaccess
│   ├── index.php
│   ├── confirmation.php
│   ├── privacy.php
│   ├── admin/
│   │   ├── index.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── detail.php
│   └── assets/
└── storage/
    ├── .htaccess
    ├── ratelimit/
    └── logs/
```

---

## 3. Panduan Instalasi & Deployment di DirectAdmin

### Langkah 1 — Membuat Database MySQL Terpisah
1. Masuk ke DirectAdmin -> **Account Manager -> MySQL Management -> Create new Database**.
2. Buat database baru khusus Client Brief (misalnya `u123_clientbrief`) dan user database baru dengan password kuat.
3. Catat nama database, username, password, dan host (`127.0.0.1` atau `localhost`).

### Langkah 2 — Mengimpor Skema SQL melalui phpMyAdmin
1. Buka **phpMyAdmin** dari DirectAdmin.
2. Pilih database baru yang barusan dibuat.
3. Buka tab **Import**, pilih file `database/schema.sql`, lalu klik **Import / Go**.
4. Pastikan tabel `admins`, `client_briefs`, dan `rate_limits` terbentuk dengan engine `InnoDB` dan collation `utf8mb4_unicode_ci`.

### Langkah 3 — Mengunggah Aplikasi & Mengatur Document Root
**Opsi A (Direkomendasikan — Folder Internal di Luar `public_html`)**:
1. Unggah `app/`, `bin/`, `database/`, `storage/`, dan `.env` ke direktori privat di luar web root, misalnya:
   `/home/<user>/domains/archondigital.web.id/client-brief-app/`
2. Unggah isi folder `public/` ke document root subdomain:
   `/home/<user>/domains/archondigital.web.id/public_html/brief/`
3. Ubah path `require_once` pada file PHP di `public/` (`index.php`, `confirmation.php`, `privacy.php`, dan `public/admin/*.php`) agar menunjuk ke lokasi folder `app/bootstrap.php` Anda, atau arahkan langsung **Document Root** subdomain `brief.archondigital.web.id` di DirectAdmin ke folder `.../client-brief/public/`.

**Opsi B (Subdomain Pointer ke `client-brief/public/`)**:
1. Unggah seluruh folder `client-brief` ke `/home/<user>/domains/archondigital.web.id/client-brief/`.
2. Pada DirectAdmin (**Subdomain Management** atau **Custom HTTPD Configurations**), arahkan document root `brief.archondigital.web.id` ke `.../client-brief/public/`.
3. Pastikan izin direktori `storage/` dan `storage/logs/` dapat ditulisi PHP (`750` / `755`), dan `.env` berizin `600` atau `640`.

### Langkah 4 — Mengisi Konfigurasi `.env`
1. Salin `.env.example` menjadi `.env` (letakkan sejajar dengan `app/` atau satu tingkat di atas `client-brief/`).
2. Isi nilai berikut:
   ```ini
   APP_ENV=production
   APP_URL=https://brief.archondigital.web.id
   MAIN_SITE_URL=https://archondigital.web.id/
   ARCHON_WHATSAPP=6285159117451

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=nama_database_anda
   DB_USER=user_database_anda
   DB_PASS=password_database_anda
   ```
3. **Pastikan `.env` tidak dapat diakses dari web**: uji dengan mengakses `https://brief.archondigital.web.id/.env` dan `https://brief.archondigital.web.id/app/config/config.php` — keduanya harus menghasilkan `403 Forbidden` atau `404 Not Found`.

### Langkah 5 — Membuat Akun Admin Pertama (Tanpa Installer Web)
Tidak ada username/password bawaan di kode sumber dan tidak ada installer web publik.

- **Cara 1 — Melalui SSH (Direkomendasikan)**:
  ```bash
  cd /path/ke/client-brief
  php bin/create-admin.php
  ```
  Ikuti prompt untuk mengisi nama, email, dan password admin (minimal 10 karakter).

- **Cara 2 — Melalui phpMyAdmin (Jika SSH Tidak Tersedia)**:
  1. Generate hash password yang valid di terminal lokal yang memiliki PHP:
     ```bash
     php -r 'echo password_hash("PasswordKuatAnda!", PASSWORD_DEFAULT) . PHP_EOL;'
     ```
  2. Jalankan query SQL berikut di phpMyAdmin pada database Client Brief:
     ```sql
     INSERT INTO admins (name, email, password_hash)
     VALUES ('Admin ArchonDigital', 'admin@domainanda.com', '$2y$10$HASHSALINAN_DARI_TERMINAL');
     ```

### Langkah 6 — Mengaktifkan HTTPS
1. Di DirectAdmin, buka **Account Manager -> SSL Certificates**.
2. Aktifkan **Free & automatic certificate from Let's Encrypt** untuk domain `brief.archondigital.web.id`.
3. Centang opsi **Force SSL with https redirect** agar seluruh akses otomatis dialihkan ke HTTPS (cookie sesi `Secure` akan aktif otomatis saat berjalan di HTTPS).

---

## 4. Pengujian Pasca-Instalasi di Hosting
1. Buka `https://brief.archondigital.web.id/` dan kirim formulir Client Brief uji coba dengan data valid.
2. Pastikan halaman konfirmasi tampil beserta nomor referensi (`BRF-YYYYMMDD-XXXXXX`).
3. Uji validasi dengan mengosongkan kolom wajib atau memasukkan nomor WhatsApp tidak valid — pastikan pesan kesalahan muncul dan data tidak tersimpan.
4. Buka `https://brief.archondigital.web.id/admin/` — pastikan diarahkan ke `/admin/login.php`.
5. Masuk menggunakan akun admin pertama, lihat brief uji coba, ubah status, simpan catatan internal, uji tombol **Hubungi via WhatsApp**, uji arsip, dan hapus brief uji coba tersebut setelah verifikasi selesai.

---

## 5. Backup & Restore Database MySQL

### Backup (phpMyAdmin / SSH)
- **phpMyAdmin**: Pilih database Client Brief -> tab **Export** -> metode **Quick (SQL)** -> **Export**.
- **SSH (`mysqldump`)**:
  ```bash
  mysqldump -u USER_DB -p NAMA_DB > backup-client-brief-$(date +%F).sql
  ```

### Restore
- **phpMyAdmin**: Pilih database kosong/tujuan -> tab **Import** -> pilih file `.sql` backup -> **Import**.
- **SSH**:
  ```bash
  mysql -u USER_DB -p NAMA_DB < backup-client-brief-YYYY-MM-DD.sql
  ```
