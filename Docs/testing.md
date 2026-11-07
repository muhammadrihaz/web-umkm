# Testing

Lapisan: PHPUnit unit/feature, migration dan locking di MySQL, HTTP smoke melalui Nginx, black-box/UAT manual.

Kasus otomatis: injeksi role, akses admin, ownership order, validasi quantity, kalkulasi harga server, idempotency checkout, konflik payload, commit/release stok dua kali, lifecycle COD, transisi fulfillment, signature dan nominal Midtrans, duplicate/out-of-order webhook, fraud challenge, expiry, late payment, refund kumulatif, dan overpayment antar-attempt. Hasil aktual dicatat di `sprint-progress.md`.

Perintah terverifikasi:

```powershell
docker compose --profile testing build test
docker compose --profile testing run --rm test php artisan test --compact
```

Pada 2026-11-06 hasil final lokal adalah `27 passed (106 assertions)` menggunakan koneksi MySQL `pangkonan_test`. Selain itu, 58 route tervalidasi, seluruh Blade berhasil dicache, dan home/health/manifest aset merespons HTTP 200. Ini bukan pengganti sandbox Midtrans maupun UAT manusia.

Sandbox Midtrans, UAT pemilik, SUS, backup/restore production, dan browser matrix tidak dinyatakan lulus tanpa bukti. Format black-box: ID FR, skenario, prasyarat, input, langkah, expected, actual, pass/fail, tester, tanggal, bukti.
