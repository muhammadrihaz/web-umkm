# E-Commerce UMKM Pangkonan

Implementasi Laravel untuk TA **“Implementasi Platform E-Commerce Berbasis Web untuk Penguatan UMKM: Studi Kasus UMKM Pangkonan di Gianyar”**. Aplikasi ini adalah toko tunggal, bukan marketplace. Data awal adalah data demo dan harus dikonfirmasi pemilik sebelum dipakai nyata.

## Stack terpilih

- Laravel 13 / PHP 8.4.
- MySQL 8.4 LTS dengan InnoDB.
- Blade, Bootstrap 5.3, Vite 8, Node 24 LTS.
- Paket resmi `midtrans/midtrans-php` 2.6.
- Nginx, PHP-FPM, queue database, dan Laravel scheduler melalui Docker Compose.

Semua timestamp disimpan UTC. Tampilan laporan menggunakan `Asia/Makassar` (WITA).

## Instalasi Docker

Prasyarat: Docker Desktop/Engine dengan Compose v2. PHP, Composer, Node, dan MySQL host tidak diperlukan.

```powershell
Copy-Item .env.example .env
```

Ganti `DB_PASSWORD`, `DB_ROOT_PASSWORD`, kredensial demo, dan konfigurasi lain di `.env`. Jangan commit `.env`. Setup otomatis yang aman diulang:

```powershell
.\docker\setup.ps1 -SeedDemo
```

Tanpa `-SeedDemo`, script tidak membuat data contoh. Langkah manual ekuivalen:

```powershell
docker compose build
docker compose up -d mysql
docker compose ps
docker compose run --rm --user root --volume "${PWD}/.env:/var/www/html/.env" app php artisan key:generate --force
docker compose run --rm app php artisan migrate --force
docker compose run --rm app php artisan db:seed --force
docker compose up -d nginx app queue scheduler
```

Aplikasi: `${APP_URL}` (default `http://localhost:8085`). Health check: `${APP_URL}/up`. Jika port tersebut terpakai, ubah `APP_PORT` dan `APP_URL` bersama-sama.

Development dengan bind mount, Vite, dan Mailpit:

```powershell
docker compose -f compose.yaml -f compose.dev.yaml --profile development up -d
docker compose -f compose.yaml -f compose.dev.yaml run --rm app composer install
docker compose -f compose.yaml -f compose.dev.yaml exec node npm run build
```

Mailpit: `http://localhost:8025`.

## Akun admin aman

`DatabaseSeeder` hanya membuat akun demo pada environment `local`/`testing`. Sebelum seeding, ubah `DEMO_ADMIN_EMAIL` dan `DEMO_ADMIN_PASSWORD`. Jangan menjalankan demo seeder di production. Registrasi publik mengabaikan input `role` dan selalu memakai default `customer`.

## Midtrans sandbox

Isi `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, dan pertahankan `MIDTRANS_IS_PRODUCTION=false`. Atur Notification URL sandbox ke `https://DOMAIN-PUBLIK/payments/midtrans/webhook`. Localhost tidak dapat dijangkau Midtrans; gunakan tunnel HTTPS atau staging. Detail ada di [Docs/payment-midtrans.md](Docs/payment-midtrans.md).

## Pengujian

```powershell
docker compose --profile testing build test
docker compose --profile testing run --rm test php artisan test --compact
docker compose --profile testing run --rm test php artisan route:list --except-vendor
docker compose --profile testing run --rm --user root --volume "${PWD}:/workspace" test vendor/bin/pint --test --format agent /workspace/app /workspace/bootstrap /workspace/config /workspace/database /workspace/routes /workspace/tests
```

Service `test` dipaksa memakai MySQL dan database `pangkonan_test`; jangan arahkan ke database operasional. Buat database uji satu kali bila belum ada:

```powershell
docker compose exec mysql mysql -uroot -pYOUR_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS pangkonan_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
docker compose --profile testing run --rm test php artisan test --compact
```

## Operasi, backup, dan troubleshooting

```powershell
docker compose logs -f app queue scheduler nginx
docker compose restart queue scheduler
docker compose exec app php artisan payments:reconcile
docker compose down
```

`down` tidak menghapus named volume. Jangan gunakan `down -v` pada data yang harus dipertahankan.

```powershell
docker compose exec -T mysql mysqldump -upangkonan -pYOUR_DB_PASSWORD pangkonan > backup.sql
docker run --rm -v pangkonan-umkm_uploads:/data -v ${PWD}:/backup alpine:3.21 tar czf /backup/uploads.tar.gz -C /data .
Get-Content backup.sql | docker compose exec -T mysql mysql -upangkonan -pYOUR_DB_PASSWORD pangkonan
```

- Port bentrok: ubah `APP_PORT`, `VITE_PORT`, atau `MAILPIT_PORT`.
- Database belum siap: tunggu `healthy`, cek `docker compose logs mysql`.
- Aset tidak tampil: rebuild image lalu recreate service.
- Upload 404: cek named volume `uploads`, alias `/storage/` pada Nginx, lalu rebuild/recreate image web.
- Webhook gagal: pastikan HTTPS publik, server key benar, lalu jalankan `payments:reconcile`.

Dokumentasi lanjutan berada di folder [Docs](Docs).
