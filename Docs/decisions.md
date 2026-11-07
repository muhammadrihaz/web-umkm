# Decision log

## 2026-11-03 — baseline

- Laravel 13: security support sampai Maret 2028.
- PHP 8.4: kompatibilitas paket matang; security support sampai akhir 2028.
- MySQL 8.4 LTS untuk stabilitas dan dukungan panjang.
- Node 24 LTS (dukungan sampai April 2028).
- Bootstrap 5.3 tanpa SPA; Midtrans Snap memakai paket resmi; production tidak diaktifkan.

Referensi resmi: `laravel.com/docs/13.x/releases`, `php.net/supported-versions.php`, `dev.mysql.com/doc/refman/8.4/en/mysql-releases.html`, dan `nodejs.org/en/blog/migrations/v22-to-v24`.

Uang memakai integer rupiah. Timestamp database UTC; laporan WITA. Cart tidak memesan stok. Checkout mereservasi atomik; stok dikurangi saat online paid terverifikasi atau COD dikonfirmasi. Refund tidak otomatis restock. Produk terpesan memakai soft delete. Seed selalu berlabel demo.
