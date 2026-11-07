# Matriks black-box

Status di bawah hanya diisi `Lulus otomatis` bila memiliki bukti PHPUnit yang dijalankan ulang pada 2026-11-06. Skenario manual tetap `Belum diuji` dan tidak boleh dianggap lulus.

| ID FR | Skenario | Prasyarat/input | Expected | Actual/status | Tester/tanggal | Bukti |
|---|---|---|---|---|---|---|
| FR-AUTH-01 | Registrasi menyuntikkan `role=admin` | Pengunjung, payload role admin | User tetap customer | Lulus otomatis | Agent / 2026-11-06 | `AuthAuthorizationTest` |
| FR-AUTH-02 | Customer membuka admin | Customer login | HTTP 403 | Lulus otomatis | Agent / 2026-11-06 | `AuthAuthorizationTest` |
| FR-ORDER-01 | Customer membuka order orang lain | Dua customer | HTTP 403 | Lulus otomatis | Agent / 2026-11-06 | `OrderOwnershipTest` |
| FR-CART-01 | Quantity nol | Produk aktif, qty 0 | Ditolak validasi | Lulus otomatis | Agent / 2026-11-06 | `CheckoutInventoryTest` |
| FR-CHECKOUT-01 | Manipulasi harga client | Harga payload Rp1 | Total memakai harga DB | Lulus otomatis | Agent / 2026-11-06 | `CheckoutInventoryTest` |
| FR-CHECKOUT-02 | Duplicate checkout | Idempotency key sama | Satu order/reservasi | Lulus otomatis | Agent / 2026-11-06 | `CheckoutInventoryTest` |
| FR-PAY-01 | Signature salah/raw amount berubah | Webhook Midtrans | Ditolak tanpa efek | Lulus otomatis | Agent / 2026-11-06 | `PaymentSignatureTest` |
| FR-PAY-02 | Duplicate/out-of-order webhook | Settlement dua kali lalu pending | Stok sekali, paid tidak turun | Lulus otomatis | Agent / 2026-11-06 | `PaymentLifecycleTest` |
| FR-PAY-03 | Expiry lalu late payment | Reservasi sudah dilepas | Paid tercatat, order exception | Lulus otomatis | Agent / 2026-11-06 | `PaymentLifecycleTest` |
| FR-PAY-04 | Partial/full refund | Nilai kumulatif gateway | Adjustment tidak ganda | Lulus otomatis | Agent / 2026-11-06 | `PaymentLifecycleTest` |
| FR-PAY-05 | Dua attempt paid | Dua transaksi terverifikasi | Stok sekali, overpayment exception | Lulus otomatis | Agent / 2026-11-06 | `PaymentLifecycleTest` |
| FR-COD-01 | COD selesai tanpa uang diterima | COD confirmed | Ditolak | Lulus otomatis | Agent / 2026-11-06 | `OrderStateTest` |
| FR-FULFILL-01 | Lompat status fulfillment | Unfulfilled langsung selesai | Ditolak | Lulus otomatis | Agent / 2026-11-06 | `OrderStateTest` |
| FR-ADMIN-01 | Halaman operasional admin | Admin login | Kategori, pelanggan, payment, zona, laporan, pengaturan render | Lulus otomatis | Agent / 2026-11-06 | `AdminPagesTest` |
| FR-UI-01 | Browser HP/desktop | Chrome/Safari/Firefox nyata | Layout dan alur usable | Belum diuji | - | Perlu browser matrix |
| FR-MIDTRANS-01 | Pembayaran sandbox nyata | Credential + HTTPS publik | Webhook/reconcile end-to-end | Belum diuji | - | Credential belum tersedia |
| FR-OPS-01 | Backup lalu restore | Environment restore terpisah | DB dan upload pulih | Belum diuji | - | Perlu target restore |
