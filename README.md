# sidigit

## Dockerized workflows

### Local development
- Copy `.env.example` to `.env` and adjust anything you need for development (defaults target PHP 8.4 on the official PHP FPM image).
- Use `make help` to discover common Docker targets.
- Start the stack with `make up` (or `docker compose -f docker-compose.local.yml up --build`).
- Install PHP dependencies inside the container: `make composer-install` (or `docker compose -f docker-compose.local.yml exec app composer install`).
- Run database migrations: `docker compose -f docker-compose.local.yml exec app php artisan migrate`.
- Handle frontend assets on the host machine: run `npm install` and `npm run dev` locally to keep Vite watching for changes.
- Your API is available on `http://localhost:8080` and the host-side Vite server runs on `http://localhost:5173`.
- Point the `AWS_*` values in `.env` at your existing MinIO/S3 endpoint (the compose file defaults to `http://host.docker.internal:9000`; on Linux set this to the accessible host or network alias).

### Production image & deployment
- Review `docker/.env.production` and replace the placeholder values with your real production secrets before building.
- Build the images with `docker compose -f docker-compose.prod.yml build`.
- Push them to your registry (for example `docker tag sidigit-app:latest registry.example.com/sidigit-app:latest`).
- Run the stack where you deploy: `docker compose -f docker-compose.prod.yml up -d`.
- After the containers start, run `docker compose -f docker-compose.prod.yml exec app php artisan key:generate --force` (first run), `php artisan migrate --force`, and `php artisan storage:link` if you need public storage.
- Make sure `docker/.env.production` references the correct external MinIO/S3 endpoint and credentials before deploying.

## Order
- Ditambahkan modul **Tracking Order Publik** dengan URL: `/track/order/{id_order_encrypted}`.
- Link tracking bersifat **public**: siapa pun yang memiliki URL dapat melihat progres order.
- ID order di URL tidak memakai ID mentah, tetapi token terenkripsi melalui `OrderTrackingToken`.
- Implementasi mengikuti **service and repository pattern**:
  - `App\\Services\\OrderTrackingService`
  - `App\\Repositories\\OrderTrackingRepository`
- Halaman tracking publik menampilkan status saat ini dan riwayat pengerjaan dari `order_status_logs`.
- Akses tracking dipindahkan ke **Daftar Order** pada kolom **Tracking** (aksi `Lihat` dan `Salin Link`) agar header halaman detail order tetap ringkas.
- Aksi **Salin Link** menampilkan **toast sukses** (bukan modal) agar feedback cepat dan tidak mengganggu alur kerja.
- Untuk environment `http` (non-HTTPS), salin link tetap dicoba otomatis via fallback `execCommand('copy')`; prompt manual hanya muncul jika browser menolak semua metode copy.
