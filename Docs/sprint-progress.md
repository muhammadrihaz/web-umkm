# Sprint progress

Checkpoint 2026-11-06.

| Sprint | Status | Hasil | Blocker/berikutnya |
|---|---|---|---|
| 0 Analisis | Selesai | FR, ERD, state, keputusan | Validasi data pemilik |
| 1 Fondasi | Implemented/test | Docker runtime, auth, role, layout, seed idempotent | Validasi data pemilik |
| 2 Produk/stok | Implemented/test | CRUD kategori/produk/foto, katalog, ledger/service | Validasi katalog pemilik |
| 3 Checkout | Implemented/test | Cart, server totals, snapshot, MySQL locks, idempotency, reserve/commit/release ledger | Uji beban concurrency paralel |
| 4 Midtrans | Implemented/test lokal | Adapter, signature, durable webhook, reconcile, expiry/late payment/refund/overpayment | Credential + HTTPS sandbox |
| 5 Operasional | Implemented/test | COD idempotent, transisi fulfillment, cancel guard, WhatsApp, exception | UAT pemilik |
| 6 Laporan | Implemented/test | WITA, gross receipts, refund, COD, CSV formula guard | Validasi definisi bisnis pemilik |
| 7 QA | Otomatis lulus | MySQL: 27 test, 106 assertion; Pint bersih; 58 route dan seluruh Blade valid | Browser matrix/UAT/sandbox manual |
| 8 Deploy | Runtime lokal lulus | Compose valid; MySQL healthy; app/queue/scheduler/Nginx aktif; home/health/assets HTTP 200 | Staging HTTPS + restore nyata |

Jangan menyatakan sandbox, UAT, SUS, backup/restore, atau production lulus sampai benar-benar diuji.
