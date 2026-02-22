
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

### Catatan
- UI **branch switcher** sudah dibuat (session `active_branch_id`).
- `branch_id` diambil dari branch aktif (session) atau fallback user login.
- CRUD cabang mendukung upload **logo** & **QRIS**.
- Storage logo/QRIS untuk cabang dipaksa ke disk `public`.
- Logo cabang tampil di **sidebar**.
- QRIS cabang tampil di **invoice** dan **invoice-pdf**.
- Tampilan QRIS di **invoice-pdf** dijaga proporsinya (tidak gepeng) dengan wrapper table-cell.
- Form cabang menampilkan rekomendasi ukuran logo (64x64 atau 128x128 px, horizontal 64x308 px).
- Modal form sekarang **scrollable** agar tombol aksi tetap bisa diakses saat konten panjang.
- Dropdown multiselect di modal dinaikkan z-index agar tampil di atas overlay.
- Users: opsi **Akun tanpa pegawai** + relasi `employee_id` (nullable) di users.
- Toggle akun tanpa pegawai dibuat **instant** dengan Alpine (x-show + entangle).
- Saat pilih pegawai, **username & email** otomatis terisi. Email kosong akan diisi manual lalu disalin ke `mst_employees.email`.
- Nama user juga **auto-fill** dari pegawai saat dipilih.
- Posisi toggle akun tanpa pegawai + pilihan pegawai dipindah ke bagian atas form user.
- Branch switcher: superadmin bisa pilih **Semua Cabang** (tanpa session), dan switch memaksa reload agar data benar-benar terfilter.
- Branch switcher sekarang melakukan **full reload** setelah ganti cabang agar data pasti sesuai branch aktif.
- Branch switcher kembali ke `wire:model` standar untuk update branch.
- ProductSeeder sekarang **static** (nama produk, kategori, bahan bisa diatur langsung di array).
- PermissionSeeder: tambah izin `stock-reservation.view` + RolePermissionSeeder dirapikan (kasir dapat akses reservasi stok).
- ProductSeeder: perbaikan scope variabel saat mapping material.
- UserSeeder: role kasir memakai `Kasir`, admin tetap dibuat meski role kasir tidak ada.
- BranchSeeder pakai `updateOrCreate` agar nama cabang induk bisa diperbarui; default nama induk = `(Headquarter)`.
- Orders: tombol aksi di Edit Order ditambah **Buat Quotation**, **Approve Quotation**, **Buat Invoice**, dan **Print Invoice** (visible sesuai status).
- Orders Table: aksi **Buat Invoice** ditambahkan (hanya untuk status draft/approval).
- Orders: status dropdown di form dirapikan sesuai flow (tambah status Diambil).
- Orders Create: quick action **Simpan Draft** dan **Simpan & Buat Quotation**.
- Invoice hanya bisa dibuat/print untuk status `draft` atau `approval` (validasi di controller + aksi tabel/edit).
- Orders Table: aksi print diganti jadi **Lihat Quotation** dan **Lihat Invoice** (menuju halaman detail agar user bisa pilih print/pdf).
- Order status konsisten memakai **approval** (hapus `approve`). Ditambah migrasi normalisasi untuk data existing.
- Orders Table: aksi **Lihat Invoice** tampil untuk semua status kecuali `draft`, `quotation`, `approval`.
- Laporan baru **Laporan Per Cabang** (`reports/branches`): filter periode + cabang, ringkasan **order/omzet/HPP/laba kotor/pembayaran/piutang/pengeluaran/laba bersih**, serta tabel breakdown performa per cabang.
- Seeder menu & permission diperbarui untuk laporan cabang: menu **Laporan Per Cabang** + izin `report.branch.view`.
- RolePermissionSeeder disesuaikan: role **Kasir** sekarang mendapat akses menu/izin laporan (`report.sales.view`, `report.expense.view`, `report.branch.view`) agar selaras dengan menu Laporan.
- Audit Log disempurnakan untuk tracing: tabel menampilkan **ID Log**, **User + ID**, dan **Objek + Subject ID**; filter ditambah **ID Objek**; modal detail menampilkan metadata teknis (Activity ID, Event, Subject, Subject ID, User ID, Log Name).
- Audit Log (Detail Perubahan) disederhanakan: metadata kini tampil lebih ringkas (format list), dan menampilkan **User**, **IP**, serta **URL** untuk mempermudah tracing request.
- Trait `LogsAllActivity` ditingkatkan: log baru otomatis menyimpan metadata request ke `properties.meta` (`url`, `ip`, `user`, `method`) agar informasi pada audit log lebih lengkap.
- Paket tracing inti di Audit Log diimplementasikan: `properties.meta` kini mencatat **request_id**, **route_name**, **user_agent**, **active_branch_id**, **subject_branch_id**, dan **business_key** (selain URL/IP/User/Method).
- Modal Audit Log (Detail Perubahan) sekarang menampilkan data tracing inti tersebut agar investigasi lintas request/cabang lebih cepat.
- Penyesuaian UX audit log: `changed_fields` dikeluarkan dari metadata (karena sudah tercermin di blok Data Lama/Data Baru), dan layout metadata dipadatkan menjadi grid ringkas agar tidak memakan ruang setengah halaman.
- Modal **Detail Audit Log** dibuat **vertical scrollable** (`max-height` viewport + `overflow-y-auto`) agar konten panjang tetap bisa diakses tanpa keluar layar.
- Perbaikan URL audit log untuk Livewire: jika request berasal dari endpoint `livewire/*`, metadata `url` sekarang memakai header `referer` (URL halaman asli), bukan URL internal `/livewire/update`.
- Hardening permission di level route: middleware baru `route.permission` (berbasis mapping route -> permission slug) diterapkan pada seluruh route `auth`, sehingga akses URL langsung tetap tervalidasi permission.
- Gate admin diperkuat: bypass `Gate::before` sekarang mencakup slug role `admin`, `administrator`, dan `superadmin`.
- Catatan maintainability `route.permission`: mapping di `config/route_permissions.php` **wajib ditambah manual** jika (1) nama route non-standar, mis. `stocks.in` / `orders.invoice.pdf`, (2) resource baru belum terdaftar di `resource_prefixes`, atau (3) action baru belum terdaftar di `resource_actions`. Jika route mengikuti pola standar dan prefix+action sudah terdaftar, mapping permission akan ter-resolve otomatis.
