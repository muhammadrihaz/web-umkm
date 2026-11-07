# Kebutuhan dan acceptance criteria

| ID | Kebutuhan | Acceptance criteria ringkas | Status |
|---|---|---|---|
| FR01 | Beranda/katalog | Cari produk aktif, detail, pagination | Implemented |
| FR02 | Akun/profil/reset | Registrasi selalu customer dan sesi aman | Implemented |
| FR03 | Keranjang | Persistent, item unik, quantity positif | Implemented |
| FR04 | Checkout | Total server, row lock, snapshot, idempotency | Implemented |
| FR05 | Midtrans | Snap resmi, signed webhook + GET Status | Implemented; sandbox blocked |
| FR06 | COD | Toggle, konfirmasi, penerimaan uang satu kali | Implemented |
| FR07 | Produk/foto | SKU unik, upload tervalidasi, arsip | Implemented, termasuk UI kategori |
| FR08 | Stok | Formula available, adjustment dan ledger | Implemented |
| FR09 | Operasional | Filter, COD, fulfillment dengan guard | Implemented baseline |
| FR10 | Riwayat | Hanya pemilik/admin melihat order | Implemented |
| FR11 | Pelanggan | Relasi customer-order | Implemented, termasuk master dan histori |
| FR12 | Pickup/delivery | Zona server-side, status dibatasi metode | Implemented |
| FR13 | WhatsApp | Tautan kontekstual berbasis config | Implemented |
| FR14 | Laporan/CSV | WITA→UTC, income valid, CSV aman | Implemented baseline |
| FR15 | Rekonsiliasi | Durable event, dedup, retry, paid monotonic | Implemented; sandbox belum diuji |

Data yang masih perlu pemilik: definisi produk, ukuran/satuan, pre-order, umur simpan, kapasitas, cutoff, zona/tarif, kebijakan COD/cancel/refund, WhatsApp, dan alamat lengkap.
