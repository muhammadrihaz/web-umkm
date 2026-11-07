# Deployment

Gunakan image immutable dari `compose.yaml`, secret dari `.env`/secret manager, `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, serta volume `mysql_data` dan `uploads`. Migration adalah langkah release eksplisit; setelah deploy jalankan `queue:restart`. Jangan migrate pada setiap startup.

Urutan: build → backup → migrate `--force` → recreate app/nginx/queue/scheduler → health/smoke test. MySQL tidak dipublikasikan. Hanya satu scheduler baseline. Webhook harus HTTPS publik dan trusted proxy dibatasi. Restore diuji di environment terpisah.
