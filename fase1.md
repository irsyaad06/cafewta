# FASE 1 - ADMIN MASTER DATA, MIGRATION, MODEL & FILAMENT CRUD

## Project

Nama project: `cafewta`

Jenis aplikasi: Sistem Operasional Cafe berbasis web.

Stack utama:
- Laravel 12
- PHP 8.3+
- Vue 3 + Inertia.js sudah tersedia, tetapi tidak digunakan pada fase ini
- Filament 3
- MySQL
- Role statis menggunakan kolom `users.role`

Status project saat ini:
- Project Laravel 12 sudah dibuat.
- Project sudah bisa dijalankan.
- Filament 3 sudah terpasang.
- Fokus fase ini adalah backend admin dan master data.

---

## Tujuan Fase 1

Fase ini berfokus untuk membangun pondasi admin panel menggunakan Filament 3.

Fokus utama:
- Membuat migration data master.
- Membuat model dan relasi data master.
- Membuat enum role/status dasar.
- Membuat Filament Resource CRUD untuk data master.
- Membuat grouping sidebar Filament.
- Menyiapkan akses admin berbasis role statis.
- Menyiapkan seed data awal jika diperlukan.

Fase ini tidak membahas frontend customer, POS kasir, kitchen display, UI/UX custom, API publik, ataupun integrasi realtime.

---

## Scope Fase 1

Kerjakan hanya bagian berikut:

1. Migration data master.
2. Model data master.
3. Relationship antar model.
4. Enum dasar.
5. Seeder awal untuk user admin dan data referensi.
6. Filament Resource CRUD untuk data master.
7. Sidebar group Filament.
8. Role statis pada user.
9. Akses Filament berdasarkan role.
10. Form dan table Filament untuk CRUD data master.

---

## Out of Scope Fase 1

Jangan kerjakan hal berikut:

- Jangan membuat frontend Vue/Inertia.
- Jangan membuat halaman customer.
- Jangan membuat POS kasir.
- Jangan membuat kitchen display.
- Jangan membuat order transaction flow.
- Jangan membuat payment gateway.
- Jangan membuat laporan analytics final.
- Jangan membuat API untuk frontend.
- Jangan membuat realtime system.
- Jangan membuat loyalty/member.
- Jangan membuat multi cabang.
- Jangan membuat dashboard custom yang kompleks.
- Jangan menggunakan Spatie Permission.
- Jangan membuat role/permission dinamis.

---

# 1. Role Statis

Project ini menggunakan role statis.

Role disimpan pada kolom:

```txt
users.role
```

Role awal:

```txt
super_admin
owner
admin_manager
cashier
kitchen
waiter
customer
```

Gunakan enum:

```txt
App\Enums\UserRole
```

Contoh enum:

```php
<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';
    case AdminManager = 'admin_manager';
    case Cashier = 'cashier';
    case Kitchen = 'kitchen';
    case Waiter = 'waiter';
    case Customer = 'customer';
}
```

Aturan:
- Jangan gunakan Spatie Permission.
- Jangan buat tabel `roles`.
- Jangan buat tabel `permissions`.
- Jangan buat pivot permission.
- Role cukup dari kolom `users.role`.
- Akses dicek lewat enum, middleware, policy, atau method pada model User.

Role yang boleh akses Filament:
- `super_admin`
- `owner`
- `admin_manager`

Role lain tidak boleh masuk panel admin.

---

# 2. Update Tabel Users

Tambahkan field berikut pada tabel `users`:

```txt
role
phone
is_active
```

Detail field:
- `role`: string atau enum, default `admin_manager`
- `phone`: nullable string
- `is_active`: boolean, default true

Model User harus memiliki cast:
- `is_active` ke boolean
- `role` ke `UserRole` jika menggunakan enum cast

User hanya boleh login Filament jika:
- `is_active = true`
- role termasuk `super_admin`, `owner`, atau `admin_manager`

---

# 3. Master Data yang Dibuat

Fase ini fokus pada master data cafe.

Master data yang wajib dibuat:

1. Category
2. Menu
3. Supplier
4. Raw Material
5. Recipe
6. Table
7. Payment Method
8. Expense Category

Opsional jika masih sempat:
9. Unit
10. Tax / Service Setting

---

# 4. Database Schema

## 4.1 categories

Untuk kategori menu.

Table name:

```txt
categories
```

Fields:

```txt
id
name
slug
description nullable
is_active boolean default true
sort_order unsigned integer default 0
timestamps
softDeletes
```

Relasi:
- Category has many Menu

---

## 4.2 menus

Untuk data menu makanan/minuman yang dijual.

Table name:

```txt
menus
```

Fields:

```txt
id
category_id foreignId constrained categories cascadeOnUpdate restrictOnDelete
name
slug
sku nullable unique
description nullable
image nullable
selling_price decimal(15,2)
hpp decimal(15,2) default 0
is_available boolean default true
is_active boolean default true
sort_order unsigned integer default 0
timestamps
softDeletes
```

Relasi:
- Menu belongs to Category
- Menu has many Recipe

Catatan:
- `selling_price` adalah harga jual.
- `hpp` boleh diinput manual pada fase ini.
- Perhitungan HPP otomatis dari recipe belum wajib pada fase ini.

---

## 4.3 suppliers

Untuk pemasok bahan baku.

Table name:

```txt
suppliers
```

Fields:

```txt
id
name
phone nullable
email nullable
address nullable
note nullable
is_active boolean default true
timestamps
softDeletes
```

Relasi:
- Supplier has many RawMaterial

---

## 4.4 raw_materials

Untuk bahan baku cafe.

Table name:

```txt
raw_materials
```

Fields:

```txt
id
supplier_id nullable foreignId constrained suppliers nullOnDelete
name
sku nullable unique
unit
stock decimal(15,3) default 0
minimum_stock decimal(15,3) default 0
buy_price decimal(15,2) default 0
is_active boolean default true
timestamps
softDeletes
```

Relasi:
- RawMaterial belongs to Supplier
- RawMaterial has many Recipe

Catatan:
- `unit` menggunakan string dulu, contoh: gram, ml, pcs, kg, liter.
- Master unit terpisah bersifat opsional.

---

## 4.5 recipes

Untuk komposisi bahan baku pada menu.

Table name:

```txt
recipes
```

Fields:

```txt
id
menu_id foreignId constrained menus cascadeOnDelete
raw_material_id foreignId constrained raw_materials restrictOnDelete
quantity decimal(15,3)
unit
note nullable
timestamps
```

Relasi:
- Recipe belongs to Menu
- Recipe belongs to RawMaterial

Catatan:
- Recipe dipakai untuk HPP dan auto stock deduction pada fase berikutnya.
- Pada fase ini cukup CRUD relasi menu dan bahan baku.

---

## 4.6 tables

Untuk meja cafe dan QR menu.

Karena `table` adalah istilah umum, gunakan nama model `CafeTable`.

Table name:

```txt
cafe_tables
```

Fields:

```txt
id
table_number unique
name nullable
capacity unsigned integer nullable
qr_code nullable
status string default available
is_active boolean default true
timestamps
softDeletes
```

Status awal:
```txt
available
occupied
reserved
inactive
```

Relasi:
- CafeTable akan digunakan pada order di fase berikutnya.

---

## 4.7 payment_methods

Untuk metode pembayaran.

Table name:

```txt
payment_methods
```

Fields:

```txt
id
name
code unique
type string
is_active boolean default true
sort_order unsigned integer default 0
timestamps
softDeletes
```

Type awal:

```txt
cash
qris
transfer
debit
ewallet
```

---

## 4.8 expense_categories

Untuk kategori pengeluaran operasional.

Table name:

```txt
expense_categories
```

Fields:

```txt
id
name
slug
description nullable
is_active boolean default true
sort_order unsigned integer default 0
timestamps
softDeletes
```

Contoh:
- Listrik
- Air
- WiFi
- Gas
- Gaji
- Maintenance
- Bahan habis pakai

---

## 4.9 units Optional

Jika ingin dibuat sebagai master terpisah.

Table name:

```txt
units
```

Fields:

```txt
id
name
symbol
description nullable
is_active boolean default true
timestamps
softDeletes
```

Contoh:
- gram / gr
- kilogram / kg
- mililiter / ml
- liter / l
- pieces / pcs

Jika master unit dibuat, field `unit` pada raw_materials dan recipes tetap boleh string dulu agar sederhana.

---

# 5. Model yang Dibuat

Buat model berikut:

```txt
Category
Menu
Supplier
RawMaterial
Recipe
CafeTable
PaymentMethod
ExpenseCategory
```

Opsional:

```txt
Unit
```

Setiap model wajib:
- Menggunakan namespace standar Laravel.
- Menggunakan `HasFactory`.
- Menggunakan `SoftDeletes` untuk data master utama.
- Mendefinisikan `$fillable`.
- Mendefinisikan cast boolean dan decimal.
- Mendefinisikan relationship.

---

## 5.1 Relationship Model

Category:

```txt
hasMany Menu
```

Menu:

```txt
belongsTo Category
hasMany Recipe
```

Supplier:

```txt
hasMany RawMaterial
```

RawMaterial:

```txt
belongsTo Supplier
hasMany Recipe
```

Recipe:

```txt
belongsTo Menu
belongsTo RawMaterial
```

CafeTable:

```txt
belum ada relasi fase ini
```

PaymentMethod:

```txt
belum ada relasi fase ini
```

ExpenseCategory:

```txt
belum ada relasi fase ini
```

---

# 6. Enum yang Dibuat

Buat enum berikut:

```txt
App\Enums\UserRole
App\Enums\TableStatus
App\Enums\PaymentMethodType
```

## UserRole

Value:

```txt
super_admin
owner
admin_manager
cashier
kitchen
waiter
customer
```

## TableStatus

Value:

```txt
available
occupied
reserved
inactive
```

## PaymentMethodType

Value:

```txt
cash
qris
transfer
debit
ewallet
```

---

# 7. Filament Sidebar Group

Buat grouping sidebar Filament agar rapi.

Gunakan navigation group berikut:

```txt
Dashboard
Master Data
Inventory
Finance
Settings
```

Untuk fase 1, resource ditempatkan sebagai berikut:

## Dashboard

```txt
Dashboard
```

## Master Data

```txt
Categories
Menus
Cafe Tables
Payment Methods
```

## Inventory

```txt
Suppliers
Raw Materials
Recipes
Units optional
```

## Finance

```txt
Expense Categories
```

## Settings

```txt
Users
```

Aturan:
- Gunakan label yang jelas.
- Gunakan icon Filament/Heroicons yang sesuai.
- Gunakan sort order agar sidebar rapi.
- Jangan buat navigation group terlalu banyak.

---

# 8. Filament Resource yang Wajib Dibuat

Buat Filament Resource untuk:

```txt
CategoryResource
MenuResource
SupplierResource
RawMaterialResource
RecipeResource
CafeTableResource
PaymentMethodResource
ExpenseCategoryResource
UserResource
```

Opsional:

```txt
UnitResource
```

---

# 9. Ketentuan Filament Resource

Setiap resource wajib memiliki:

- Form schema
- Table columns
- Search
- Sort
- Filter aktif/nonaktif
- Edit action
- Delete action
- Bulk delete jika aman
- Soft delete handling untuk data master yang memakai SoftDeletes

Jangan membuat Blade manual.

Gunakan bawaan Filament 3:
- Forms
- Tables
- Resources
- Pages
- Actions

---

# 10. Detail Form Filament

## CategoryResource Form

Fields:
- name
- slug auto dari name
- description
- is_active
- sort_order

Table:
- name
- slug
- is_active
- sort_order
- created_at

Filter:
- is_active

---

## MenuResource Form

Fields:
- category_id select relationship
- name
- slug auto dari name
- sku
- description
- image upload
- selling_price
- hpp
- is_available
- is_active
- sort_order

Table:
- image
- name
- category.name
- selling_price
- hpp
- margin display optional
- is_available
- is_active
- created_at

Filter:
- category
- is_available
- is_active

Catatan:
- Margin boleh dibuat sebagai calculated column:
  `selling_price - hpp`
- Persentase margin optional.

---

## SupplierResource Form

Fields:
- name
- phone
- email
- address
- note
- is_active

Table:
- name
- phone
- email
- is_active
- created_at

Filter:
- is_active

---

## RawMaterialResource Form

Fields:
- supplier_id select relationship nullable
- name
- sku
- unit
- stock
- minimum_stock
- buy_price
- is_active

Table:
- name
- supplier.name
- unit
- stock
- minimum_stock
- buy_price
- stock_status badge
- is_active

Filter:
- supplier
- is_active
- low stock optional

Catatan:
- Stock status:
  - low jika stock <= minimum_stock
  - safe jika stock > minimum_stock

---

## RecipeResource Form

Fields:
- menu_id select relationship
- raw_material_id select relationship
- quantity
- unit
- note

Table:
- menu.name
- rawMaterial.name
- quantity
- unit
- note

Filter:
- menu
- raw_material

Catatan:
- Recipe bisa juga nanti dijadikan RelationManager di MenuResource.
- Pada fase 1 boleh tetap dibuat Resource sendiri agar mudah CRUD.

---

## CafeTableResource Form

Fields:
- table_number
- name
- capacity
- qr_code
- status
- is_active

Table:
- table_number
- name
- capacity
- status badge
- is_active
- created_at

Filter:
- status
- is_active

Catatan:
- QR generation otomatis belum wajib.
- Field qr_code boleh nullable.

---

## PaymentMethodResource Form

Fields:
- name
- code
- type
- is_active
- sort_order

Table:
- name
- code
- type
- is_active
- sort_order

Filter:
- type
- is_active

---

## ExpenseCategoryResource Form

Fields:
- name
- slug auto dari name
- description
- is_active
- sort_order

Table:
- name
- slug
- is_active
- sort_order
- created_at

Filter:
- is_active

---

## UserResource Form

Fields:
- name
- email
- password
- role
- phone
- is_active

Table:
- name
- email
- role
- phone
- is_active
- created_at

Filter:
- role
- is_active

Aturan password:
- Saat create, password wajib.
- Saat edit, password nullable.
- Jika password kosong saat edit, jangan ubah password lama.
- Password harus di-hash.

---

# 11. Seeder Awal

Buat seeder awal:

```txt
AdminUserSeeder
MasterDataSeeder
```

## AdminUserSeeder

Buat user awal:

```txt
name: Super Admin
email: admin@cafewta.test
password: password
role: super_admin
is_active: true
```

## MasterDataSeeder

Isi data awal:

Categories:
- Coffee
- Non Coffee
- Food
- Snack
- Dessert

Payment Methods:
- Cash
- QRIS
- Transfer
- Debit

Expense Categories:
- Listrik
- Air
- WiFi
- Gas
- Gaji
- Maintenance
- Bahan Habis Pakai

Units optional:
- gram
- kilogram
- ml
- liter
- pcs

---

# 12. Akses Filament

Pastikan hanya role berikut yang bisa masuk Filament:

```txt
super_admin
owner
admin_manager
```

Implementasi:
- Gunakan method `canAccessPanel` pada model User jika dibutuhkan oleh Filament.
- Cek `is_active`.
- Cek role user.

Contoh logic:

```txt
user aktif DAN role termasuk super_admin/owner/admin_manager
```

---

# 13. Prinsip Coding

Wajib:
- Gunakan bahasa Inggris untuk nama table, model, class, dan field.
- Gunakan migration yang rapi.
- Gunakan foreign key constraint.
- Gunakan soft delete untuk data master utama.
- Gunakan enum untuk status/type.
- Gunakan model relationship.
- Gunakan fillable.
- Gunakan cast.
- Gunakan Filament Resource bawaan.
- Jangan buat Blade manual.
- Jangan buat frontend.
- Jangan buat API.
- Jangan buat fitur transaksi.
- Jangan buat fitur inventory movement.
- Jangan buat laporan final.

---

# 14. Output Akhir Fase 1

Setelah fase 1 selesai, project harus memiliki:

- Migration master data selesai.
- Model master data selesai.
- Relationship antar model selesai.
- Enum dasar selesai.
- User role statis selesai.
- Seeder admin dan master data selesai.
- Filament Resource CRUD data master selesai.
- Sidebar group Filament rapi.
- Akses Filament berdasarkan role statis.
- Project tetap bisa berjalan dengan `composer run dev`.
- Admin dapat login ke `/admin`.
- CRUD master data dapat digunakan dari Filament.

---

# 15. Checklist Selesai

Fase 1 dianggap selesai jika:

- `php artisan migrate:fresh --seed` berhasil.
- Login admin berhasil.
- `/admin` dapat dibuka.
- Sidebar memiliki group:
  - Master Data
  - Inventory
  - Finance
  - Settings
- CRUD Category berjalan.
- CRUD Menu berjalan.
- CRUD Supplier berjalan.
- CRUD Raw Material berjalan.
- CRUD Recipe berjalan.
- CRUD Cafe Table berjalan.
- CRUD Payment Method berjalan.
- CRUD Expense Category berjalan.
- CRUD User berjalan.
- Tidak ada frontend custom yang dibuat.
