
# 📝 TODO — MVP System


## 🧭 Multi-Branch (Implementasi Awal)
### Ringkasan
- **Model cabang**: 1 company = 1 database, dengan cabang induk (`is_main`).
- **branch_id wajib** di data transaksi & master yang berbeda per cabang.
- **Admin/superadmin** dapat melihat semua cabang (bypass scope).
- **User biasa** dibatasi oleh `branch_id` default.

### Struktur Baru
- Tabel **`branches`** (nama, alamat, telp, email, logo, qris, `is_main`).
- Pivot **`branch_user`** untuk akses multi cabang.
- Kolom **`branch_id`** di `users` (default cabang induk).

### Scoping Otomatis
- Trait `BranchScoped` + Global Scope `BranchScope`.
- Saat create, `branch_id` otomatis dari user login.

### Tabel yang ditambahkan `branch_id`
- `orders`, `payments`, `expenses`, `stock_movements`
- `mst_customers`, `mst_suppliers`, `mst_materials`, `mst_products`
- `mst_categories`, `mst_units`, `mst_warehouses`
- `mst_employees`, `employee_attendances`, `employee_loans`
- `mst_bank_accounts`, `finishes`

### Seeder
- `BranchSeeder` membuat cabang induk.
- `UserSeeder` menetapkan cabang induk ke semua user.

### Validasi Unik (per cabang)
- Category, Unit, Product SKU, Customer email, Employee email, Bank Account.

## Catatan

## Branch
- UI **branch switcher** sudah dibuat (session `active_branch_id`).
- `branch_id` diambil dari branch aktif (session) atau fallback user login.
- Branch switcher: superadmin bisa pilih **Semua Cabang** (tanpa session), lalu switch memaksa reload agar data benar-benar terfilter.
- Branch switcher melakukan **full reload** setelah ganti cabang agar data pasti sesuai branch aktif.
- Branch switcher kembali ke `wire:model` standar untuk update branch.
- CRUD cabang mendukung upload **logo** & **QRIS**.
- Storage logo/QRIS untuk cabang dipaksa ke disk `public`.
- Logo cabang tampil di **sidebar**.
- QRIS cabang tampil di **invoice** dan **invoice-pdf**.
- Tampilan QRIS di **invoice-pdf** dijaga proporsinya (tidak gepeng) dengan wrapper table-cell.
- Form cabang menampilkan rekomendasi ukuran logo (64x64 atau 128x128 px, horizontal 64x308 px).
- Modal form sekarang **scrollable** agar tombol aksi tetap bisa diakses saat konten panjang.
- Dropdown multiselect di modal dinaikkan z-index agar tampil di atas overlay.
- BranchSeeder pakai `updateOrCreate` agar nama cabang induk bisa diperbarui; default nama induk = `(Headquarter)`.

## User
- Users: opsi **Akun tanpa pegawai** + relasi `employee_id` (nullable) di users.
- Toggle akun tanpa pegawai dibuat **instant** dengan Alpine (x-show + entangle).
- Saat pilih pegawai, **username** dan **email** otomatis terisi.
- Jika email pegawai kosong, email diisi manual di form user lalu disalin ke `mst_employees.email`.
- Nama user juga **auto-fill** dari pegawai saat dipilih.
- Posisi toggle akun tanpa pegawai + pilihan pegawai dipindah ke bagian atas form user.
- UserSeeder: role kasir memakai `Kasir`, admin tetap dibuat meski role kasir tidak ada.
- UserSeeder: ditambahkan akun kasir default statis (`name: kasir`, `username: kasir`, `email: kasir@gmail.com`) dan otomatis dihubungkan ke role `Kasir` + cabang induk.

## Product
- ProductSeeder sekarang **static** (nama produk, kategori, bahan bisa diatur langsung di array).
- ProductSeeder: perbaikan scope variabel saat mapping material.

## Order
- Orders: tombol aksi di Edit Order ditambah **Buat Quotation**, **Approve Quotation**, **Lihat Invoice**, dan **Print Invoice** (visible sesuai status).
- Orders: status dropdown di form dirapikan sesuai flow (tambah status `diambil`).
- Orders Create: quick action **Simpan Draft** dan **Simpan & Buat Quotation**.
- Invoice hanya bisa diakses/dicetak mulai status **Approval** ke atas (status `draft` dan `quotation` ditolak).
- Orders Table: aksi print diganti jadi **Lihat Quotation** dan **Lihat Invoice** (menuju halaman detail agar user bisa pilih print/pdf).
- Order status konsisten memakai **approval** (hapus `approve`), termasuk migrasi normalisasi data existing.
- Orders Table: aksi **Lihat Invoice** tampil mulai status **Approval** ke atas.
- Rule invoice diperbaiki: invoice hanya bisa diakses mulai status **Approval** ke atas; status `draft` dan `quotation` tidak bisa melihat/mencetak invoice.
- Aksi order diperbarui agar konsisten dengan rule baru: tombol/aksi **Lihat Invoice** muncul mulai status Approval, dan pesan validasi invoice di controller diperjelas.
- Redirect setelah Create Order disesuaikan dengan status: `draft` ke halaman edit order, `quotation` ke halaman quotation, dan status Approval ke atas ke halaman invoice.
- Akar masalah pembayaran setelah order tersimpan ditemukan di UI form (`$isEditing`) yang menyembunyikan aksi tambah pembayaran saat mode edit.
- Ditambahkan halaman/action khusus **Input Pembayaran** (`orders/{order}/payments`) dengan tombol akses dari **Orders Table**, **Edit Order**, dan panel pembayaran di form edit.
- `OrderService` ditambah method `addPayment()` untuk menambah pembayaran tanpa re-save seluruh item; setelah insert, total pembayaran & status bayar direkalkulasi otomatis.
- Perhitungan pembayaran diperbarui: jika total bayar melebihi grand total, selisih dicatat sebagai **Kembalian** (bukan sisa negatif) dan ditampilkan di ringkasan order, halaman input pembayaran, invoice, serta invoice PDF.
- Tampilan **Riwayat Pembayaran** diubah ke format **buku tabungan (debit/kredit/saldo berjalan)**: ada baris pembuka tagihan (debit), setiap pembayaran masuk sebagai kredit, kolom saldo menampilkan sisa tagihan per transaksi, dan footer total menampilkan **Total Tagihan / Total Bayar / Sisa Tagihan**.
- Halaman input pembayaran ditingkatkan: field **Jumlah** kini memakai format **Rupiah** (masking), dan ditambahkan tombol **Lunas** untuk auto-fill nominal **Sisa Tagihan** ke field jumlah.
- Validasi pembayaran diperketat: field **Jumlah** dan tombol simpan otomatis **disabled** saat `sisa_tagihan <= 0`, serta backend memblok submit pembayaran tambahan; kondisi minus dijelaskan sebagai **kembalian**.
- Penyesuaian styling dark mode halaman input pembayaran yang sempat diuji sudah **di-rollback**; tampilan kembali ke style dark mode awal (standar TailAdmin).
- Penyelarasan UI halaman pembayaran: background section **Input Pembayaran** dan **Riwayat Pembayaran** disamakan dengan kartu ringkasan (contoh: **Order No**) agar visual lebih konsisten.
- Order Edit: mulai status **Approval** ke atas, halaman menjadi **read-only** (mode lihat); seluruh perubahan data order dipindahkan ke alur aksi terkontrol.
- Hardening backend lock order: `OrderService::update()` memblok update field/item/payment jika status existing sudah fase **Approval+**; hanya perubahan status yang diproses.
- Revisi status terkontrol: saat status diturunkan dari fase **Approval+** ke tahap sebelumnya, field **Alasan Revisi** wajib diisi pada action **Ubah Status** di daftar order.
- Validasi wajib alasan revisi ditegakkan di level service (`OrderService`) agar tidak bisa di-bypass dari request manual; alasan tersimpan pada `order_status_logs.note` untuk kebutuhan tracing.
- UX Order disempurnakan: pesan validasi dibuat lebih **human-readable** (label field lebih jelas, bahasa natural), dan field **Alasan Revisi** muncul **realtime** di modal Ubah Status saat status di-downgrade.
- Workflow baru setelah Approval: halaman `Edit Order` berubah menjadi mode **Lihat Order** (read-only), tombol Simpan disembunyikan, dan user diarahkan mengubah status dari daftar order.
- Action button pada mode **Lihat Order** dirapikan urutannya: **Lihat Invoice**, **Lihat Quotation**, **Print Invoice**, **Print Quotation**, **Input Pembayaran**, **Kembali**; tiap tombol dilengkapi ikon representatif.
- Orders Table ditambah action **Ubah Status** (modal): pilih status baru langsung dari list order, dengan validasi **alasan revisi wajib** saat downgrade dari fase Approval+.
- Action bar order diperbarui: status `draft`/`quotation` menampilkan **Edit Order**, sedangkan status Approval+ menampilkan **Lihat Order**.
- Urutan action di dropdown **Orders Table** dirapikan (mode Approval+): **Input Pembayaran**, **Ubah Status**, **Lihat Order**, **Lihat Quotation**, **Lihat Invoice**, **Delete**.
- Halaman detail order (Invoice/Quotation) kini menampilkan **Riwayat Status** dari `order_status_logs` (waktu, status, user, catatan), sehingga alasan revisi bisa ditelusuri langsung dari UI tanpa cek database.

## Reporting
- Laporan baru **Laporan Per Cabang** (`reports/branches`): filter periode + cabang, ringkasan **order/omzet/HPP/laba kotor/pembayaran/piutang/pengeluaran/laba bersih**, serta tabel breakdown performa per cabang.
- Seeder menu & permission diperbarui untuk laporan cabang: menu **Laporan Per Cabang** + izin `report.branch.view`.
- RolePermissionSeeder disesuaikan: role **Kasir** mendapat akses laporan (`report.sales.view`, `report.expense.view`, `report.branch.view`).

## Audit Log
- Audit Log disempurnakan untuk tracing: tabel menampilkan **ID Log**, **User + ID**, dan **Objek + Subject ID**; filter ditambah **ID Objek**.
- Modal detail audit log menampilkan metadata teknis: Activity ID, Event, Subject, Subject ID, User ID, Log Name.
- Audit Log (Detail Perubahan) disederhanakan: metadata dibuat lebih ringkas dan menampilkan **User**, **IP**, **URL**.
- Trait `LogsAllActivity` ditingkatkan: metadata request disimpan ke `properties.meta` (`url`, `ip`, `user`, `method`).
- Paket tracing inti ditambahkan ke `properties.meta`: **request_id**, **route_name**, **user_agent**, **active_branch_id**, **subject_branch_id**, **business_key**.
- Modal Audit Log menampilkan data tracing inti untuk investigasi lintas request/cabang.
- `changed_fields` dikeluarkan dari metadata (karena sudah tercermin di Data Lama/Data Baru).
- Layout metadata dipadatkan jadi grid ringkas agar tidak memakan setengah halaman.
- Modal **Detail Audit Log** dibuat **vertical scrollable** (`max-height` viewport + `overflow-y-auto`).
- Untuk request Livewire (`livewire/*`), metadata `url` memakai header `referer` (URL asli halaman), bukan `/livewire/update`.

## Permission & Security
- PermissionSeeder: tambah izin `stock-reservation.view`; RolePermissionSeeder dirapikan agar kasir dapat akses reservasi stok.
- Route permission diperbarui: `orders.payments.create` dipetakan ke izin `order.edit`.
- Hardening permission level route: middleware `route.permission` (mapping route -> permission slug) diterapkan pada seluruh route `auth`.
- Gate admin diperkuat: bypass `Gate::before` mencakup slug role `admin`, `administrator`, dan `superadmin`.
- RolePermissionSeeder kasir disederhanakan: menu yang tampil hanya **Transaksi -> Order** dengan permission kasir fokus ke `order.view`, `order.create`, `order.edit`.
- Maintainability `route.permission`: mapping di `config/route_permissions.php` **wajib ditambah manual** jika (1) nama route non-standar, contoh `stocks.in`/`orders.invoice.pdf`, (2) resource baru belum ada di `resource_prefixes`, atau (3) action baru belum ada di `resource_actions`.
- Jika route mengikuti pola standar dan prefix+action sudah terdaftar, mapping permission akan ter-resolve otomatis.
