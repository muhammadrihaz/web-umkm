# Skenario UAT

Dokumen ini adalah rencana UAT, bukan bukti UAT telah dilakukan.

1. Pemilik mengoreksi nama, harga, unit, stok, foto, zona, ongkir, kontak, serta kebijakan COD dari data demo.
2. Customer HP mendaftar, mencari produk, mengubah keranjang, memilih pickup, lalu menyelesaikan COD.
3. Customer delivery memilih zona dan alamat; sistem menghitung ulang ongkir dan total.
4. Customer membayar via Midtrans sandbox, menutup browser, lalu membuka ulang halaman Pesanan; status berasal dari server.
5. Admin memproses pickup dan delivery sesuai urutan status, serta tidak dapat menyelesaikan COD sebelum uang diterima.
6. Admin meninjau order exception, duplicate payment, challenge, refund, dan CSV laporan pada batas tanggal WITA.
7. Operator melakukan restart app/queue/scheduler, memeriksa health/log, lalu mencoba backup dan restore ke environment terpisah.

Catat untuk tiap skenario: tester, peran, perangkat/browser, tanggal, data input, expected, actual, pass/fail, komentar, dan bukti. Skor SUS hanya dikumpulkan bila disetujui metodologi penelitian.
