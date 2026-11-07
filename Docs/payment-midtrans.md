# Midtrans Snap

Attempt mempunyai `provider_order_id` unik; nomor order bisnis tetap. Network call baru dilakukan setelah transaksi checkout selesai.

Webhook `/payments/midtrans/webhook`: validasi field dan SHA512 dengan `gross_amount` asli; simpan event durable/dedup; GET Status backend; cocokkan order ID, transaction ID, dan nominal; proses melalui `PaymentService`. Kegagalan diproses ulang oleh `payments:reconcile` tiap 5 menit. GET Status 404/exception tidak menurunkan status. Browser redirect bukan bukti bayar.

Kredensial sandbox dan URL HTTPS publik belum tersedia sehingga integrasi nyata belum diklaim lulus.
