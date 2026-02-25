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

## Paket Aplikasi (Rencana Modular)
### Matriks Paket (Saran)
| Modul/Fitur | Silver (Rp200.000) | Gold (Rp300.000) | Platinum (Rp350.000) |
|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ |
| Customer | ✅ | ✅ | ✅ |
| Order + Quotation + Invoice | ✅ | ✅ | ✅ |
| Input Pembayaran | ✅ | ✅ | ✅ |
| Tracking Order Publik | ✅ | ✅ | ✅ |
| Stok (in/out/opname/saldo) | ❌ | ✅ | ✅ |
| Pengeluaran (bahan & umum) | ❌ | ✅ | ✅ |
| Laporan (sales & expense) | ❌ | ✅ | ✅ |
| Akuntansi (COA + Jurnal Umum) | ❌ | ✅ | ✅ |
| Audit Logs | ❌ | ❌ | ✅ |
| Multi Branch | ❌ | ❌ | ✅ |
| RBAC lanjutan (role/permission/menu custom) | ❌ | ❌ | ✅ |
| Laporan cabang | ❌ | ❌ | ✅ |

### Skema Praktis (Dikelola Superadmin Internal)
- Akun `superadmin` dipegang internal (tidak diberikan ke klien).
- Klien hanya menerima menu dan permission sesuai paket aktif.
- Akses final = user punya permission + fitur paket aktif.
- Konsep data yang disarankan:
  - `features`
  - `packages` (silver/gold/platinum)
  - `package_feature` (default fitur per paket)
  - `client_feature_overrides` (opsional ON/OFF khusus per klien)
- Implementasi teknis bertahap:
  - fase 1: kontrol menu + route middleware berbasis fitur
  - fase 2: guard fitur di service/action agar tidak bisa bypass API/livewire

## Role Owner (Override)
- Role sistem utama diganti dari `Administrator` menjadi `Superadmin`.
- Seeder default membuat akun superadmin:
  - `name`: `Superadmin User`
  - `username`: `superadmin`
  - `email`: `superadmin@gmail.com`
  - `password`: `password`
- Ditambahkan role baru: `Owner` (slug otomatis: `owner`).
- Tujuan role `Owner`: hak akses operasional tertinggi di sisi klien tanpa memberikan akun `superadmin`.
- Seeder default membuat akun:
  - `name`: `owner`
  - `username`: `owner`
  - `email`: `owner@gmail.com`
  - `password`: `password`
- Permission override workflow yang ditambahkan:
  - `workflow.override.status`
  - `workflow.override.actor`
  - `workflow.override.locked-order`
- `Owner` mendapatkan seluruh menu + permission tenant melalui `RolePermissionSeeder`.
- Penugasan role `Owner` dibatasi:
  - hanya akun dengan role `superadmin` yang dapat menetapkan role `Owner` pada form user.
  - non-superadmin tidak melihat opsi role `Owner` pada form user.
- Untuk order yang statusnya sudah terkunci (`approval` ke atas), user dengan permission `workflow.override.locked-order` dapat tetap melakukan edit penuh order.

## Feature Gate
- Ditambahkan infrastruktur feature gate dasar untuk klasifikasi paket:
  - `config/feature_gate.php`
  - `App\Support\FeatureGate`
  - middleware `App\Http\Middleware\EnsureFeatureEnabled`
  - alias middleware route: `route.feature`
- Cara pakai di route:
  - contoh: `Route::get('/orders', ...)->middleware('route.feature:orders');`
- Cara menambah feature baru:
  1. Tambahkan key pada `config/feature_gate.php` bagian `features`.
  2. (Opsional) Atur override role di `role_overrides`.
  3. Pasang middleware `route.feature:{nama_feature}` pada route yang relevan.
- Bypass default:
  - role slug pada `bypass_role_slugs` (default: `superadmin`) selalu lolos pengecekan feature gate.

## Order
- Tracking order publik menggunakan URL: `/track/order/{id_order_encrypted}`.
- Link tracking bersifat public dan memakai token terenkripsi (`OrderTrackingToken`), bukan ID mentah.
- Akses link tracking dipindahkan ke Daftar Order kolom **Tracking** (aksi `Lihat` dan `Salin Link`) agar header halaman detail order tetap ringkas.
- Aksi **Salin Link** menampilkan toast sukses.
- Untuk environment `http` (non-HTTPS), salin link tetap dicoba otomatis via fallback `execCommand('copy')`; prompt manual hanya muncul jika browser menolak semua metode copy.

## UI Sidebar
- Sidebar sekarang memakai map icon SVG bergaya TailAdmin dari `config/menu.php`.
- Kompatibilitas icon lama tetap aman: nilai menu `bi bi-*` otomatis dikonversi ke SVG, jadi icon tetap muncul tanpa ubah data menu lama.
- Resolver icon memiliki fallback default sehingga menu tanpa mapping tidak tampil kosong.
- Icon `Profile` dan `Logout` pada blok **Account** diganti ke SVG agar konsisten dengan gaya TailAdmin.
- Versi cache sidebar dinaikkan ke `v3` agar data menu lama tidak mengunci icon kosong.
- Form **Tambah/Edit Menu** sekarang mendukung input icon manual (`icon`) dan pilihan preset icon key.
- Nilai icon dari form diprioritaskan (jika tidak ada mapping) agar custom class/key yang diinput user tetap tersimpan dan dirender.
- Styling sidebar dirapikan agar lebih mirip TailAdmin: ikon tanpa kotak border, state aktif/inaktif lebih clean, dan indent submenu lebih proporsional.
- Styling **submenu** ikut disamakan: panel dropdown diberi spacing yang konsisten, item aktif/nonaktif mengikuti tone TailAdmin, dan area klik item dibuat lebih rapi.

## Akuntansi
- Ditambahkan modul **Akuntansi (inti)**:
  - **Dashboard Akuntansi** (`/accounting/overview`) untuk ringkasan posisi akun utama.
    - Mendukung filter periode `Harian`, `Bulanan`, dan `Custom Range`.
    - Tabel **Jurnal Terbaru** dirapikan (spacing kolom + nowrap nominal/user) agar mudah dibaca.
  - **Arus Kas** (`/accounting/cashflows`) untuk melihat pemasukan dan pengeluaran dalam satu halaman.
    - Menampilkan `Saldo Awal`, `Total Masuk`, `Total Keluar`, `Net Cashflow`, `Saldo Akhir`.
    - Menyediakan tabel mutasi kronologis dengan saldo berjalan.
    - Mendukung filter `Periode`, `Sumber (all/payment/expense)`, dan `Metode (cash/transfer/qris)`.
  - **Chart of Accounts** (`/accounting/accounts`) untuk kelola akun per cabang.
  - **Jurnal Umum** (`/accounting/journals`) untuk input jurnal manual dengan validasi debit = kredit.
- Implementasi menggunakan service and repository pattern:
  - `App\\Services\\AccountingAccountService`
  - `App\\Services\\AccountingJournalService`
  - `App\\Repositories\\AccountingAccountRepository`
  - `App\\Repositories\\AccountingJournalRepository`
- Struktur data baru:
  - `acc_accounts`
  - `acc_journals`
  - `acc_journal_lines`
- Seeder default COA:
  - `AccountingAccountSeeder` membuat akun standar awal pada cabang induk.
- Auto posting transaksi:
  - `Payment` otomatis membentuk jurnal:
    - Sebelum status `selesai`: Debit Kas/Bank (`1001`/`1002`), Kredit Uang Muka Pelanggan (`2003`)
    - Setelah status `selesai`: Debit Kas/Bank (`1001`/`1002`), Kredit Piutang Usaha (`1101`)
    - Jika lebih bayar: selisih ke Hutang Kembalian Pelanggan (`2002`)
  - Saat order berubah ke status `selesai`, sistem membuat jurnal pengakuan pendapatan (accrual):
    - Debit Uang Muka Pelanggan (`2003`) dan/atau Debit Piutang Usaha (`1101`)
    - Kredit Pendapatan Penjualan (`4001`)
    - Debit HPP (`5001`) dan Kredit Persediaan Bahan (`1201`) jika nilai HPP > 0
  - Jika status order diturunkan dari `selesai`, jurnal accrual order akan disinkronkan (dihapus dari sumber `order-accrual`) agar tidak salah saji.
  - `Expense` otomatis membentuk jurnal:
    - Expense material: Debit Persediaan Bahan (`1201`), Kredit Kas/Bank
    - Expense umum: Debit Beban Operasional (`6001`), Kredit Kas/Bank
  - Expense `update/delete` akan sinkron/hapus jurnal sumber terkait (`source_type=expense`).
- RBAC akuntansi:
  - permission baru: `accounting-overview.view`, `cashflow.view`, `account.*`, `journal.view`, `journal.create`
  - menu baru: **Akuntansi** -> **Dashboard Akuntansi**, **Arus Kas**, **Chart of Accounts**, **Jurnal Umum**
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
- Status `dibatalkan` sekarang diperlakukan sebagai status **locked/read-only** seperti `approval` ke atas.
- Pada status locked (termasuk `dibatalkan`), update dari halaman Edit Order hanya mengizinkan perubahan status via aksi daftar order; field lain tidak diproses.
- Opsi status `dibatalkan` tetap tersedia di UI perubahan status untuk kasus order batal (mis. Draft/Quotation tidak jadi lanjut).

## Testing
- Ditambahkan regression test untuk flow lock status `dibatalkan`:
  - `tests/Feature/Orders/CancelledOrderLockingTest.php`
  - mencakup:
    - halaman edit tampil read-only untuk order `dibatalkan`
    - update non-status diabaikan untuk user tanpa override `workflow.override.locked-order`
    - opsi `dibatalkan` tampil di modal ubah status (daftar order)
- `AuthServiceProvider` ditambah guard `Schema::hasTable('permissions')` agar test environment tidak gagal saat bootstrap sebelum migrasi selesai.
