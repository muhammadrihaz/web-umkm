Anda adalah Senior Laravel Engineer, System Analyst, dan QA Engineer yang bertugas membangun aplikasi website e-commerce untuk Tugas Akhir saya dari awal sampai siap didemonstrasikan.

Kerjakan implementasinya secara nyata pada repository/workspace yang tersedia. Jangan berhenti pada penjelasan atau pembuatan rencana. Laksanakan sprint secara berurutan, jalankan pengujian, perbaiki kegagalan, dan dokumentasikan hasilnya.

# 1. KONTEKS PROYEK

Judul TA:
“Implementasi Platform E-Commerce Berbasis Web untuk Penguatan UMKM: Studi Kasus UMKM Pangkonan di Gianyar”.

Objek:
UMKM Pangkonan milik Ibu Komang di kawasan Jalan Raya Pantai Lebih, Gianyar, Bali.

Masalah bisnis:
- Informasi produk belum tersaji secara terstruktur.
- Pemesanan dilakukan langsung dan melalui WhatsApp.
- Detail pesanan tersebar dalam percakapan dan catatan manual.
- Stok diperiksa serta dicatat secara manual.
- Data pelanggan dan riwayat pesanan belum terintegrasi.
- Rekap penjualan dan pemasukan membutuhkan pengerjaan manual.

Tujuan aplikasi:
1. Menyediakan katalog dan pemesanan daring.
2. Memudahkan komunikasi pelanggan dengan penjual.
3. Mengelola produk, stok, pelanggan, pesanan, dan pembayaran.
4. Menyediakan laporan transaksi serta pemasukan yang dapat ditelusuri.
5. Menjadi implementasi yang dapat diuji dan didemonstrasikan untuk TA.

Sistem hanya untuk satu usaha, bukan marketplace banyak penjual.

Metode penelitian tetap Prototype:
analisis → rancangan awal → implementasi → demonstrasi → feedback → perbaikan.

Sprint adalah pembagian pekerjaan teknis. Jangan mengganti metode penelitian menjadi Scrum tanpa instruksi saya.

# 2. KEPUTUSAN TEKNOLOGI

Gunakan:
- Laravel sebagai backend.
- MySQL dengan InnoDB sebagai database.
- Blade, Bootstrap, dan Vite sebagai frontend.
- JavaScript secukupnya untuk interaksi.
- Midtrans Snap melalui paket PHP resmi untuk pembayaran online.
- Docker Compose untuk instalasi, pengembangan, pengujian, dan deployment.
- Session authentication untuk website satu domain.
- Queue database sebagai baseline.
- Laravel scheduler untuk pekerjaan terjadwal.

Pilih versi Laravel, PHP, MySQL, Node, dan dependency yang masih didukung dan saling kompatibel berdasarkan dokumentasi resmi saat implementasi.

Jika repository sudah memiliki versi yang layak, periksa dahulu sebelum memutuskan upgrade. Catat versi terpilih dan alasannya.

Jangan menggunakan:
- Xendit.
- Supabase.
- Backend Node.js terpisah.
- Arsitektur microservices.
- SPA framework tambahan tanpa kebutuhan nyata.

Bahasa antarmuka: Indonesia.
Mata uang: IDR.
Penyimpanan timestamp: UTC secara konsisten.
Tampilan waktu dan periode laporan: WITA, Asia/Makassar.

# 3. KEPUTUSAN BISNIS DAN ASUMSI

Baseline implementasi:
- Barang dijual berdasarkan stok barang siap jual.
- Satu SKU mewakili satu produk/satuan/varian yang bisa dibeli.
- Pembayaran penuh melalui Midtrans.
- COD tersedia sebagai alternatif yang dapat dinonaktifkan admin.
- Penerimaan barang melalui pickup atau delivery lokal.
- Ongkir berdasarkan zona dengan tarif yang dikelola admin.
- Komunikasi menggunakan tautan WhatsApp kontekstual.
- Pelanggan harus login sebelum checkout.
- Pengunjung dapat melihat katalog.

Informasi yang belum dipastikan:
- Jenis bahan dan definisi detail produk Pangkonan.
- Satuan dan ukuran produk sebenarnya.
- Apakah sebagian produk pre-order.
- Umur simpan dan aturan stok harian.
- Kapasitas produksi per tanggal.
- Batas waktu pemesanan/pengambilan.
- Area serta tarif delivery.
- Ketentuan pembatalan, COD, dan refund.
- Nomor WhatsApp dan alamat lengkap usaha.

Jangan mengarang informasi tersebut sebagai fakta.

Untuk pengembangan:
- Gunakan data demo yang jelas diberi label.
- Jadikan informasi usaha dan tarif sebagai konfigurasi.
- Catat asumsi di docs/decisions.md.
- Jangan menjanjikan tanggal pemenuhan yang belum memiliki aturan.
- Jika ternyata bisnis wajib pre-order, dokumentasikan perubahan model kapasitas dan dampaknya sebelum memfinalkan checkout.

Lanjutkan bagian yang independen dari keputusan yang belum tersedia. Hanya tanyakan hal yang benar-benar menghalangi implementasi atau berisiko merusak data.

# 4. AKTOR DAN HAK AKSES

Pengunjung:
- Melihat beranda.
- Melihat katalog.
- Mencari produk.
- Melihat detail produk.
- Melihat informasi usaha.
- Menghubungi WhatsApp.
- Registrasi dan login.

Customer:
- Mengelola profil dan alamat.
- Mengelola keranjang.
- Checkout.
- Memilih pickup/delivery.
- Memilih Midtrans/COD jika tersedia.
- Melakukan atau melanjutkan pembayaran.
- Melihat riwayat dan detail pesanan miliknya.
- Memantau status pembayaran dan pemenuhan.
- Mengajukan pembatalan sesuai kebijakan.
- Menghubungi penjual terkait pesanan.

Admin:
- Mengelola kategori dan produk.
- Mengelola foto serta status aktif produk.
- Mengelola stok dan histori penyesuaian.
- Memeriksa pelanggan serta histori pesanannya.
- Memproses pesanan.
- Mengonfirmasi COD.
- Mencatat penerimaan uang COD.
- Memperbarui status pemenuhan.
- Menangani pembatalan dan exception pembayaran.
- Melihat laporan dan mengekspor CSV.
- Mengelola zona delivery dan informasi usaha.

Otorisasi harus diterapkan di backend.
Menyembunyikan tombol saja tidak cukup.
Registrasi publik tidak boleh membuat akun admin.

# 5. FITUR DAN IDENTITAS KEBUTUHAN

Gunakan ID berikut untuk keterlacakan:

FR01: Beranda, katalog, pencarian, dan detail produk.
FR02: Registrasi, login, logout, profil, dan pemulihan akun.
FR03: Keranjang belanja.
FR04: Checkout dan reservasi stok.
FR05: Pembayaran Midtrans.
FR06: Pembayaran COD.
FR07: Pengelolaan kategori, produk, dan foto.
FR08: Pengelolaan stok dan histori pergerakan.
FR09: Pengelolaan pesanan oleh admin.
FR10: Riwayat dan detail pesanan pelanggan.
FR11: Data pelanggan dan riwayat pembeliannya.
FR12: Pickup dan delivery lokal.
FR13: Komunikasi WhatsApp.
FR14: Dashboard, laporan, dan ekspor.
FR15: Rekonsiliasi serta pemulihan transaksi pembayaran.

Fitur di luar baseline:
- Marketplace.
- Aplikasi mobile native.
- Chat realtime.
- WhatsApp Business API.
- Integrasi kurir nasional.
- GPS tracking.
- Voucher dan loyalty.
- Akuntansi lengkap.
- Pengelolaan bahan baku dan produksi.
- Refund otomatis melalui API.

Walaupun refund API otomatis tidak dibuat, aplikasi harus dapat mengenali dan mencatat refund yang telah terjadi secara terverifikasi.

# 6. DOCKER WAJIB

Host tidak perlu memasang PHP, Composer, Node.js, atau MySQL.
Prasyarat host adalah Docker Engine/Docker Desktop dengan Docker Compose, serta Git bila diperlukan.

Service:

1. nginx
- Pintu akses aplikasi.
- Document root public/ Laravel.
- Meneruskan PHP ke app.
- Dapat membaca aset dan upload publik yang diperlukan.

2. app
- PHP-FPM.
- Ekstensi Laravel dan MySQL yang diperlukan.
- Composer tersedia.
- Menjalankan aplikasi Laravel.

3. mysql
- MySQL InnoDB.
- Named volume untuk data.
- Healthcheck.
- User aplikasi khusus.
- Tidak memublikasikan port database secara default.

4. queue
- Image aplikasi yang sama.
- Menjalankan queue worker.
- Restart policy dan graceful shutdown.
- Job aman terhadap retry.

5. scheduler
- Image aplikasi yang sama.
- Menjalankan scheduler Laravel.
- Satu instance untuk deployment baseline.

6. node
- Tooling npm/Vite/build.
- Profile development bila sesuai.
- Vite dev server hanya untuk development.
- Production menggunakan aset yang sudah dibuild.

7. mailpit
- Opsional untuk email lokal.
- Profile development.

File minimum:
- Dockerfile.
- compose.yaml.
- Override/profile development bila diperlukan.
- Konfigurasi Nginx.
- .dockerignore.
- .env.example.
- Script setup yang aman dijalankan ulang jika dibutuhkan.
- docs/deployment.md.

Aturan Docker:
- Jangan memakai tag latest.
- DB_HOST=mysql, bukan localhost.
- DB_PORT memakai port internal service.
- Jangan memasukkan secret ke Dockerfile, build argument yang bocor, image layer, atau git.
- APP_KEY hanya dibuat jika belum tersedia.
- Migration dan seeding adalah langkah setup/release eksplisit, bukan dijalankan pada setiap startup.
- Jangan menggunakan chmod 777.
- Atur permission storage/ dan bootstrap/cache dengan benar.
- Tangani UID/GID atau kepemilikan file development.
- Pastikan bind mount tidak menutupi vendor/aset tanpa mekanisme yang jelas.
- Upload produk menggunakan penyimpanan persisten.
- Production tidak menggunakan bind mount source development.
- Jangan menjalankan Vite dev server di production.
- Data bertahan setelah docker compose down/up tanpa penghapusan volume.
- Jangan menggunakan down -v atau migrate:fresh pada data yang harus dipertahankan.
- Database harus healthy sebelum setup yang bergantung padanya.
- Aplikasi tetap menangani kegagalan koneksi setelah startup.
- Pastikan worker direstart secara benar setelah deployment kode.

Semua Composer, npm, artisan, dan pengujian dijalankan melalui container.

# 7. ARSITEKTUR KODE

Gunakan:
- Controller tipis.
- Form Request untuk validasi.
- Policy untuk role dan kepemilikan objek.
- Eloquent untuk model.
- Service/action untuk transaksi bisnis.
- Enum atau konstanta terpusat untuk status.
- Adapter untuk gateway.
- Jobs/commands untuk proses tertunda dan rekonsiliasi.

Komponen minimum:
- CheckoutService.
- InventoryService.
- PaymentService.
- MidtransGateway.
- OrderStatusService.
- SalesReportService.

Webhook, rekonsiliasi, dan tindakan admin yang relevan harus menggunakan aturan bisnis terpusat agar perilakunya konsisten.

Hindari abstraksi berlebihan yang tidak diperlukan oleh aplikasi satu toko.

# 8. MODEL DATA

Rancang migration, foreign key, unique constraint, index, dan relasi.

Entitas minimum:
- users.
- addresses.
- categories.
- products.
- product_images jika foto lebih dari satu.
- carts.
- cart_items.
- delivery_zones.
- orders.
- order_items.
- payments.
- payment_notifications.
- stock_reservations.
- stock_movements.
- order_status_histories.
- payment_adjustments.
- audit_logs.
- settings.

Ketentuan:
- users memiliki role admin/customer.
- SKU unik.
- Cart item unik untuk kombinasi cart dan produk.
- Nomor order unik.
- Checkout idempotency key unik dalam lingkup customer.
- Provider order ID unik per payment attempt.
- Simpan provider transaction ID bila tersedia.
- Order menyimpan snapshot item, harga, satuan, penerima, kontak, alamat, dan ongkir.
- Uang memakai integer rupiah; jangan float.
- Nilai desimal dari gateway dinormalisasi secara presisi.
- Produk yang pernah dipesan diarsipkan/soft delete.
- Jangan menghapus histori pembayaran dan stok melalui CRUD biasa.
- Jangan mengubah total order berbayar melalui edit langsung.

Index disesuaikan dengan pencarian pelanggan, status, tanggal, nomor order, provider order ID, dan expiry reservasi.

# 9. ATURAN STOK DAN CHECKOUT

Rumus:
available_stock = stock_on_hand - stock_reserved.

Constraint:
- stock_on_hand >= 0.
- stock_reserved >= 0.
- stock_reserved <= stock_on_hand.
- quantity merupakan integer positif.

Cart tidak mencadangkan stok.

Checkout:
1. Validasi customer dan input.
2. Hitung ulang harga serta ongkir di backend.
3. Dalam transaksi DB, kunci stok produk menurut urutan ID yang konsisten.
4. Validasi produk aktif dan available_stock.
5. Buat order, snapshot, serta reservasi.
6. Simpan idempotency key.
7. Commit database.
8. Baru lakukan komunikasi jaringan dengan Midtrans.

Jangan memegang row lock saat menunggu API eksternal.

Contoh:
Stok 10, reserved 0.
Checkout 3 → stok 10, reserved 3, tersedia 7.
Pembayaran online sah atau konfirmasi COD → stok 7, reserved 0.
Saat barang dikirim/diambil, stok tidak dikurangi lagi.

Release reservasi harus idempotent.
Commit stok harus idempotent.
Setiap efek mempunyai referensi unik dan histori.

Jangan release reservasi online hanya karena timer lokal habis.
Pastikan transaksi gateway tidak lagi dapat dibayar melalui mekanisme yang sesuai.

Jika pembayaran sah datang setelah stok dilepas:
- Catat pembayaran yang benar-benar masuk.
- Validasi/reservasi ulang stok secara atomik bila memungkinkan.
- Bila stok tidak cukup, tahan fulfillment dan tampilkan exception.
- Jangan mengubah paid menjadi unpaid untuk menyembunyikan masalah.

Refund bukan alasan otomatis untuk restock.
Barang sudah dibuat khusus, rusak, atau terkirim perlu keputusan admin.

# 10. STATE MACHINE

Pisahkan:

Order:
- awaiting_payment.
- awaiting_confirmation.
- confirmed.
- processing.
- completed.
- cancelled.
- exception.

Payment:
- unpaid.
- initializing.
- pending.
- challenge.
- paid.
- failed.
- expired.
- cancelled.
- partially_refunded.
- refunded.

Fulfillment:
- unfulfilled.
- preparing.
- ready_for_pickup.
- picked_up.
- out_for_delivery.
- delivered.

Aturan:
- Online: pembayaran sah + stok valid → confirmed.
- Online unpaid tidak boleh diproses.
- COD: konfirmasi admin → confirmed dan commit reservasi.
- COD boleh diproses saat unpaid, tetapi baseline completed mensyaratkan uang diterima.
- Pickup tidak memakai status out_for_delivery.
- Delivery tidak memakai ready_for_pickup.
- Event pending lama tidak menurunkan paid.
- Refund dicatat sebagai adjustment dan perubahan status yang sesuai.
- Cancellation setelah paid memerlukan penanganan admin, bukan menghapus pembayaran.
- Semua transisi penting mempunyai actor/source, timestamp, dan alasan bila relevan.

# 11. MIDTRANS

Gunakan Snap konvensional sesuai dokumentasi resmi paket PHP.
Jangan mencampur implementasi BI-SNAP dengan Snap popup/redirect tanpa alasan.

Environment:
- MIDTRANS_SERVER_KEY.
- MIDTRANS_CLIENT_KEY.
- MIDTRANS_IS_PRODUCTION=false.
- MIDTRANS_MERCHANT_ID bila diperlukan.
- APP_URL.

Backend membaca melalui config Laravel.
Server Key tidak boleh masuk frontend.

Setiap attempt memiliki provider_order_id unik.
Nomor order bisnis tetap sama.
Hanya satu attempt aktif yang masih dapat dibayar per order.

Gross amount:
subtotal item + ongkir.
Jika item_details dikirim, totalnya harus cocok dengan gross_amount.

Webhook:
1. Endpoint publik tanpa login customer.
2. Pengecualian CSRF hanya endpoint yang membutuhkan.
3. Validasi JSON dan field.
4. Verifikasi SHA512(order_id + status_code + gross_amount + ServerKey).
5. Gunakan string gross_amount asli untuk signature.
6. Bandingkan menggunakan hash_equals.
7. Konfirmasi status melalui API backend Midtrans.
8. Cocokkan order ID, transaction ID, nominal, dan environment; periksa merchant/currency jika tersedia.
9. Simpan event secara durable.
10. Proses perubahan melalui PaymentService dan transaksi DB.
11. Terapkan deduplication per event, bukan menolak semua event berikutnya hanya karena order_id sama.
12. Event gagal diproses harus dapat dicoba ulang.
13. Kirim 2xx setelah penerimaan durable atau pemrosesan selesai; jangan mengakui sukses jika event hilang.
14. Terapkan retry/backoff untuk kegagalan sementara.

Frontend onSuccess, finish redirect, atau parameter status dari browser hanya memicu pembacaan status server.

Pemetaan:
- pending → pending.
- settlement → paid setelah verifikasi; fraud_status harus accept jika tersedia.
- capture → paid hanya sesuai hasil verifikasi/fraud yang sah.
- challenge → tahan review.
- deny/failure → failed.
- expire → expired.
- cancel → cancelled.
- refund/partial_refund → adjustment terverifikasi.
- authorize/status tak dikenal → review; jangan auto-paid.

Kasus wajib:
- GET Status 404 pada Snap sebelum pemilihan channel bukan otomatis gagal.
- Timeout create transaksi bukan bukti tidak ada transaksi.
- Rekonsiliasi attempt lama sebelum membuat attempt baru.
- Duplicate webhook tidak menggandakan stok atau penerimaan.
- Out-of-order event tidak menurunkan paid.
- Dua attempt yang sama-sama paid: catat penerimaan sebenarnya, tandai kelebihan pembayaran, stok hanya committed sekali.
- Channel pembayaran mengikuti akun yang benar-benar aktif.

Webhook development:
Gunakan tunnel publik HTTPS atau domain staging.
Localhost dan hostname service Docker tidak dapat dijangkau Midtrans.
Konfigurasikan trusted proxy secara terbatas dan tepat.
Jangan mengekspos database untuk kebutuhan webhook.

# 12. LAPORAN

Sajikan terpisah:
- Jumlah/nilai order dibuat.
- Nilai produk terbayar.
- Penerimaan bruto.
- Ongkir terkumpul.
- Refund terealisasi.
- Penerimaan neto.
- COD belum dibayar.
- Produk terjual.

Aturan:
- Pending/failed/expired bukan pemasukan.
- COD masuk pemasukan saat uang diterima.
- Paid_at untuk periode penerimaan.
- Tanggal refund untuk periode refund.
- Jangan menghitung order berulang karena banyak item/attempt.
- Pembayaran ganda tidak menggandakan nilai penjualan order.
- Nilai kelebihan penerimaan harus dapat ditelusuri.
- Jangan menyebut penerimaan sebagai laba.
- Paid bukan berarti dana sudah cair ke rekening bank merchant.
- Batas laporan WITA dikonversi konsisten terhadap timestamp UTC.
- CSV harus sesuai filter layar.
- Lindungi teks CSV dari formula injection.
- Laporan hanya mencakup transaksi yang dicatat aplikasi.

# 13. HALAMAN DAN UX

Publik:
- Beranda.
- Katalog.
- Detail produk.
- Informasi usaha/kontak.
- Login dan registrasi.

Customer:
- Profil/alamat.
- Keranjang.
- Checkout.
- Halaman pembayaran.
- Riwayat pesanan.
- Detail pesanan dan status.

Admin:
- Dashboard.
- Kategori/produk.
- Stok dan histori.
- Pesanan dan detail.
- Pelanggan dan histori.
- Pembayaran/exception.
- Laporan.
- Zona delivery.
- Pengaturan usaha.

Gunakan tampilan rapi, mudah dibaca, responsif, dan konsisten.
Sediakan loading, empty, error, success, serta konfirmasi aksi penting.
Jangan menampilkan istilah teknis internal kepada pelanggan jika tidak membantu mereka.

# 14. DOKUMENTASI DAN PROGRES

Buat dan pelihara:
- README.md.
- docs/requirements.md.
- docs/decisions.md.
- docs/data-model.md.
- docs/state-machines.md.
- docs/payment-midtrans.md.
- docs/sprint-progress.md.
- docs/testing.md.
- docs/deployment.md.
- docs/user-guide.md.

Setiap task memuat:
ID, tujuan, dependensi, acceptance criteria, status, hasil tes, dan catatan.

Jika konteks agent terbatas:
- Simpan checkpoint di docs/sprint-progress.md.
- Tuliskan apa yang selesai, apa yang belum, dan langkah berikutnya.
- Pada kelanjutan, baca checkpoint dan lanjutkan; jangan mengulang proyek dari awal.

# 15. SPRINT 0 — ANALISIS DAN RANCANGAN

Kerjakan:
- Audit repository dan environment Docker.
- Petakan kebutuhan FR01–FR15.
- Rancang service Docker, network, dan volume.
- Buat ERD dan constraint.
- Rancang state machine.
- Buat alur checkout, online payment, COD, dan fulfillment.
- Susun daftar halaman.
- Buat decision log.
- Pecah pekerjaan menjadi task kecil.

Selesai jika:
- Semua FR mempunyai acceptance criteria.
- Model stok/pembayaran konsisten.
- Asumsi belum pasti ditandai.
- Rancangan dapat menjadi dasar implementasi.

# 16. SPRINT 1 — DOCKER, FONDASI, DAN AUTENTIKASI

Kerjakan:
- Dockerfile, Compose, Nginx, MySQL, app, queue, scheduler, tooling Node.
- Env example dan setup.
- Laravel berjalan melalui Nginx.
- Migration user/role.
- Registrasi/login/logout/profil.
- Layout publik/customer/admin.
- Policy dan middleware.
- Seeder demo development.
- Reset password dengan mail testing jika tersedia.

Selesai jika:
- Setup dari README berjalan tanpa runtime host.
- Database healthy dan migration berhasil.
- Aset tampil.
- Queue/scheduler terbukti berjalan.
- Customer tidak dapat mengakses admin.
- Registrasi tidak dapat menyuntik role admin.
- Tidak ada secret di repo.

# 17. SPRINT 2 — PRODUK DAN STOK

Kerjakan:
- Kategori, produk, foto.
- Katalog, pencarian, pagination, detail.
- Aktivasi/arsip produk.
- Penyesuaian stok dan ledger.
- InventoryService.
- Validasi upload.

Selesai jika:
- Harga/stok negatif ditolak.
- Stock_on_hand tidak bisa di bawah reserved.
- Produk nonaktif tidak bisa dibeli.
- Histori produk lama utuh.
- Penyesuaian memiliki actor dan alasan.
- Katalog responsif.

# 18. SPRINT 3 — CART DAN CHECKOUT

Kerjakan:
- Cart persistent.
- Pickup/delivery dan zona.
- Perhitungan total server.
- Snapshot order.
- Idempotency checkout.
- Row lock dan reservasi atomik.
- Riwayat/detail order dengan ownership.
- Fake gateway khusus testing.

Selesai jika:
- Klik ulang tidak menggandakan order.
- Key sama dengan payload berbeda ditolak konflik.
- Manipulasi harga browser tidak berlaku.
- Dua pembeli stok terakhir tidak overselling.
- Kegagalan DB tidak meninggalkan order parsial.
- Snapshot tidak berubah saat profil/katalog berubah.

# 19. SPRINT 4 — MIDTRANS

Kerjakan:
- Adapter resmi.
- Create/reuse Snap.
- Return page.
- Webhook durable.
- Verifikasi dan GET Status backend.
- Idempotency efek bisnis.
- Rekonsiliasi dan backoff.
- Expiry yang aman.
- Exception late payment/duplicate payment.

Selesai jika:
- Sandbox sukses memperbarui backend tanpa bergantung browser.
- Webhook palsu/nominal berbeda tidak melunasi.
- Event berulang tidak menggandakan efek.
- Pending lama tidak menurunkan paid.
- Timeout tidak membuat tagihan aktif kedua.
- Snap 404 ditangani benar.
- Event dapat diproses ulang setelah worker gagal.

Jika kredensial belum tersedia, selesaikan adapter dan tes terkontrol. Tandai verifikasi sandbox sebagai blocked dan lanjutkan pekerjaan independen. Jangan mengklaim integrasi nyata sudah lulus.

# 20. SPRINT 5 — OPERASIONAL PESANAN

Kerjakan:
- Dashboard dan filter pesanan.
- Konfirmasi/penolakan COD.
- Pencatatan uang COD.
- Transisi fulfillment.
- Pembatalan.
- WhatsApp kontekstual.
- Data dan histori pelanggan.
- Penanganan exception.

Selesai jika:
- Online unpaid tidak diproses.
- COD completed mensyaratkan uang diterima.
- Pengiriman tidak mengurangi stok dua kali.
- Pelunasan COD duplikat tidak menggandakan penerimaan.
- Pembatalan paid tidak menghapus pembayaran.
- Restock mengikuti kondisi barang.
- Audit tersedia.

# 21. SPRINT 6 — LAPORAN

Kerjakan:
- Ringkasan penjualan/penerimaan/refund.
- Filter periode/status/metode.
- Produk terjual.
- COD unpaid.
- CSV.
- Perhitungan WITA.
- Dataset kendali dengan hasil manual.

Selesai jika:
- Agregasi tidak double count.
- Pending/failed/expired bukan pemasukan.
- COD hanya setelah uang diterima.
- Refund lintas periode benar.
- CSV cocok dengan layar.
- Cakupan dan definisi laporan jelas.

# 22. SPRINT 7 — QA DAN EVALUASI

Kerjakan:
- Unit test logika.
- Feature test request dan otorisasi.
- Integration test MySQL.
- Uji browser HP/desktop.
- Uji sandbox nyata jika tersedia.
- Black box berbasis FR.
- Skenario UAT pemilik/customer.
- Perbaikan dan regresi.

Kasus kritis:
1. Akses order orang lain.
2. Penyuntikan role admin.
3. Manipulasi harga.
4. Quantity tidak valid.
5. Checkout konkuren.
6. Duplicate checkout.
7. Signature salah.
8. Nominal tidak cocok.
9. Duplicate webhook.
10. Settlement lalu pending.
11. Browser ditutup setelah bayar.
12. Timeout create transaksi.
13. Snap GET Status 404.
14. Expiry/cancel dan release satu kali.
15. Late payment setelah release.
16. COD unpaid.
17. COD dilunasi dua kali.
18. Refund dan partial refund.
19. Dua attempt paid.
20. Perubahan katalog/profil setelah order.
21. Agregasi multi-item/multi-attempt.
22. Batas tanggal WITA.
23. Worker/DB gagal saat webhook.
24. Backup dan restore.
25. Pembatalan barang sudah diproses.
26. Fraud challenge.

Gunakan MySQL untuk membuktikan row lock/concurrency; SQLite saja tidak cukup.

Format black box:
ID FR, skenario, prasyarat, input, langkah, expected, actual, pass/fail, tester, tanggal, bukti.

Jangan mengarang hasil uji pengguna, skor SUS, atau klaim 100%.
SUS hanya jika disetujui dalam metodologi penelitian.

# 23. SPRINT 8 — DEPLOYMENT DAN HANDOFF

Kerjakan:
- Konfigurasi production berbasis Docker.
- Deployment staging bila lingkungan tersedia.
- HTTPS dan webhook.
- APP_DEBUG=false.
- Persistent upload.
- Worker/scheduler setelah restart.
- Migration release yang aman.
- Backup database/upload dan uji restore.
- Dokumentasi pengguna.
- Diagram as-built.
- Data demo dan skenario presentasi TA.

Selesai jika:
- README dapat dipakai untuk instalasi baru.
- Docker Compose config valid.
- Runtime diuji jika Docker tersedia.
- Data dan upload bertahan setelah recreate.
- Webhook staging bekerja jika URL tersedia.
- Restore berhasil di lingkungan uji.
- Secret tidak bocor.
- Dokumen sesuai aplikasi.
- Keterbatasan dan blocker ditulis jelas.

Jangan mengaktifkan pembayaran production tanpa instruksi eksplisit. Staging sandbox sudah dapat menjadi lingkungan demo TA.

# 24. README INSTALASI WAJIB

Tuliskan perintah yang benar-benar sesuai dengan service proyek untuk:
1. Prasyarat Docker.
2. Clone/penempatan source.
3. Menyalin environment.
4. Mengisi konfigurasi.
5. Build image.
6. Instalasi dependency.
7. Menjalankan database dan menunggu healthy.
8. Generate APP_KEY sekali.
9. Migration.
10. Seeder demo development.
11. Storage link dan permission.
12. Build aset/Vite development.
13. Menjalankan aplikasi.
14. URL aplikasi dan mail testing.
15. Menjalankan test di container.
16. Membaca log.
17. Restart worker/scheduler.
18. Menghentikan aplikasi tanpa menghapus data.
19. Backup/restore.
20. Troubleshooting port, permission, database, Vite, dan webhook.

# 25. DEFINITION OF DONE

Task selesai hanya jika:
- Acceptance criteria terpenuhi.
- Implementasi nyata tersedia.
- Validasi dan otorisasi backend benar.
- Migration/config terdokumentasi.
- Tes relevan dijalankan dan hasil dilaporkan.
- Error/empty/loading state ditangani.
- Secret terlindungi.
- Dokumen progres diperbarui.
- Tidak ada klaim palsu mengenai hasil pengujian.

Mock test tidak sama dengan integrasi sandbox.
Sandbox tidak sama dengan production.
Rencana UAT tidak sama dengan UAT yang telah dilakukan.

# 26. CARA MENJALANKAN SELURUH PEKERJAAN

Mulai dari Sprint 0 dan lanjutkan secara berurutan ke Sprint 8.

Tidak perlu meminta saya mengirim prompt tiap sprint lagi.
Setelah satu sprint memenuhi acceptance criteria, lanjutkan sprint berikutnya.

Jika menemukan blocker:
- Jelaskan blocker secara spesifik.
- Catat bagian yang terhambat.
- Lanjutkan bagian lain yang tidak bergantung pada blocker.
- Jangan mengarang kredensial, data bisnis, atau hasil pengujian.
- Jangan melewati kegagalan kritis stok/pembayaran untuk mengklaim proyek selesai.

Jika Docker tidak tersedia di lingkungan agent:
- Tetap buat konfigurasi yang lengkap.
- Lakukan pemeriksaan statis yang tersedia.
- Sediakan perintah verifikasi untuk saya.
- Tandai runtime Docker belum terverifikasi.

Jangan menghapus pekerjaan pengguna, mereset database berisi data, atau menjalankan tindakan destruktif tanpa persetujuan.

Pada akhir setiap sprint berikan ringkasan singkat:
- Selesai.
- Hasil tes.
- Blocker.
- Langkah berikutnya.

Pada akhir keseluruhan pekerjaan berikan:
1. Ringkasan fitur.
2. Cara instalasi Docker.
3. Cara menjalankan aplikasi.
4. Cara membuat akun admin secara aman.
5. Cara konfigurasi Midtrans sandbox.
6. Cara menjalankan pengujian.
7. Hasil pengujian sebenarnya.
8. Panduan demo TA.
9. Daftar pekerjaan yang masih membutuhkan data/akses/validasi manusia.

Mulai sekarang dengan audit workspace dan implementasikan proyek sesuai urutan tersebut.