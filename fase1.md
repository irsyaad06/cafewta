# FASE 1 - SETUP STRUKTUR & ARSITEKTUR PROJECT

## Project

Nama project: `cafewta`

Jenis aplikasi: Sistem Operasional Cafe berbasis web.

Stack utama:
- Laravel 12
- PHP 8.3+
- Vue 3
- Inertia.js
- Tailwind CSS
- Filament 3
- MySQL
- Spatie Laravel Permission

Status project saat ini:
- Project Laravel sudah dibuat.
- Vue 3 + Inertia sudah terpasang.
- Filament 3 sudah terpasang.
- Spatie Permission sudah terpasang.
- Project sudah bisa dijalankan.

---

## Tujuan Fase 1

Fase ini hanya berfokus pada setup struktur dan arsitektur project.

Jangan membuat fitur bisnis detail dulu.

Fase ini bertujuan agar struktur project siap untuk fase berikutnya, yaitu:
- migrasi database,
- model,
- Filament Resource,
- controller,
- API,
- frontend UI.

---

## Scope Fase 1

Yang harus dikerjakan pada fase ini:

1. Merapikan struktur folder backend.
2. Merapikan struktur folder frontend.
3. Menyiapkan struktur Service Layer.
4. Menyiapkan struktur Action.
5. Menyiapkan struktur DTO.
6. Menyiapkan struktur Enum.
7. Menyiapkan struktur Policy.
8. Menyiapkan struktur Observer.
9. Menyiapkan struktur Support helper.
10. Menyiapkan struktur Trait.
11. Menyiapkan role dan permission dasar.
12. Menyiapkan route dasar.
13. Menyiapkan arsitektur Filament 3.
14. Menyiapkan arsitektur frontend Vue 3 + Inertia.
15. Menyiapkan standar coding.

---

## Out of Scope Fase 1

Jangan kerjakan hal berikut pada fase ini:

- Jangan membuat migration fitur lengkap.
- Jangan membuat model bisnis lengkap.
- Jangan membuat CRUD Filament detail.
- Jangan membuat dashboard final.
- Jangan membuat POS final.
- Jangan membuat kitchen display final.
- Jangan membuat QR menu final.
- Jangan membuat payment gateway.
- Jangan membuat inventory logic detail.
- Jangan membuat laporan keuangan final.
- Jangan membuat fitur multi cabang.
- Jangan membuat fitur loyalty/member.
- Jangan membuat fitur AI forecasting.

---

# 1. Struktur Folder Backend

Buat folder berikut jika belum ada:

```txt
app/
├── Actions/
├── DTOs/
├── Enums/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Web/
│   │   └── Api/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Observers/
├── Policies/
├── Services/
├── Support/
└── Traits/
```

---

## app/Actions

Digunakan untuk proses kecil yang spesifik dan reusable.

Contoh action yang nanti akan digunakan:
- CreateOrderAction
- CalculateOrderTotalAction
- ReduceStockFromOrderAction
- GenerateOrderCodeAction
- GenerateQrCodeAction
- CompletePaymentAction

Pada fase 1 cukup buat foldernya saja.

Boleh tambahkan `.gitkeep` agar folder masuk ke git.

---

## app/Services

Digunakan untuk business logic utama.

Contoh service yang nanti akan digunakan:
- OrderService
- TransactionService
- InventoryService
- PurchaseService
- ExpenseService
- ReportService
- PaymentService
- MenuService

Pada fase 1 cukup buat foldernya saja.

---

## app/DTOs

Digunakan untuk Data Transfer Object agar data antar layer lebih rapi.

Contoh DTO yang nanti akan digunakan:
- CreateOrderData
- CreateTransactionData
- PaymentData
- StockMovementData
- CreateExpenseData

Pada fase 1 cukup buat foldernya saja.

---

## app/Enums

Digunakan untuk status tetap agar tidak menggunakan string manual.

Enum yang wajib disiapkan nanti:
- OrderStatus
- PaymentStatus
- TransactionStatus
- PurchaseStatus
- StockMovementType
- TableStatus
- UserRole

Pada fase 1 boleh membuat enum dasar:
- OrderStatus
- PaymentStatus
- TableStatus

---

## app/Http/Controllers/Web

Digunakan untuk halaman berbasis Inertia.

Controller web yang boleh dibuat sebagai placeholder:
- CustomerMenuController
- CashierController
- KitchenController
- DashboardController

Controller hanya boleh return halaman placeholder Inertia.

Jangan isi business logic.

---

## app/Http/Controllers/Api

Digunakan untuk endpoint JSON.

Controller API yang boleh dibuat sebagai placeholder:
- MenuController
- OrderController
- TransactionController
- InventoryController
- ReportController

Pada fase 1 cukup struktur file kosong atau method index placeholder.

---

## app/Http/Requests

Digunakan untuk validasi request.

Pada fase 1 cukup buat foldernya saja.

Nanti digunakan untuk:
- StoreOrderRequest
- StoreTransactionRequest
- StoreExpenseRequest
- StoreMenuRequest
- StoreRawMaterialRequest

---

## app/Http/Resources

Digunakan untuk format response API.

Pada fase 1 cukup buat foldernya saja.

Nanti digunakan untuk:
- MenuResource
- OrderResource
- TransactionResource
- RawMaterialResource
- ExpenseResource

---

## app/Policies

Digunakan untuk authorization.

Pada fase 1 cukup pastikan folder tersedia.

Policy nanti digunakan untuk:
- MenuPolicy
- OrderPolicy
- TransactionPolicy
- RawMaterialPolicy
- ExpensePolicy
- ReportPolicy

---

## app/Observers

Digunakan untuk event model.

Pada fase 1 cukup folder.

Observer nanti digunakan untuk:
- OrderObserver
- TransactionObserver
- StockMovementObserver

---

## app/Support

Digunakan untuk helper class internal.

Contoh nanti:
- MoneyFormatter
- CodeGenerator
- DateRangeHelper
- ReportHelper

---

## app/Traits

Digunakan untuk trait reusable.

Contoh nanti:
- HasCode
- HasUuid
- HasCreatedBy
- HasStatusBadge

---

# 2. Struktur Folder Frontend

Gunakan struktur Vue 3 + Inertia berikut:

```txt
resources/js/
├── Pages/
│   ├── Customer/
│   ├── Cashier/
│   ├── Kitchen/
│   └── Dashboard/
├── Layouts/
├── Components/
│   ├── ui/
│   ├── form/
│   ├── card/
│   ├── table/
│   └── shared/
├── Composables/
├── Stores/
└── Utils/
```

---

## Pages

Buat folder:
- `resources/js/Pages/Customer`
- `resources/js/Pages/Cashier`
- `resources/js/Pages/Kitchen`
- `resources/js/Pages/Dashboard`

Pada fase 1 boleh membuat halaman placeholder:
- `Customer/Menu.vue`
- `Cashier/Index.vue`
- `Kitchen/Display.vue`
- `Dashboard/Index.vue`

Isi cukup tampilan sederhana agar route bisa dites.

---

## Layouts

Buat layout dasar:
- `CustomerLayout.vue`
- `CashierLayout.vue`
- `KitchenLayout.vue`
- `AppLayout.vue`

Layout hanya mengatur tampilan dasar.

Jangan masukkan business logic ke layout.

---

## Components

Siapkan folder komponen:
- `ui`
- `form`
- `card`
- `table`
- `shared`

Pada fase 1 boleh buat komponen dasar:
- `Button.vue`
- `Card.vue`
- `Badge.vue`

---

## Composables

Siapkan folder untuk reusable logic Vue.

Contoh nanti:
- useCart.js
- useCurrency.js
- useOrderStatus.js
- useRealtimeOrder.js

Pada fase 1 cukup buat folder.

---

## Stores

Gunakan Pinia untuk state management.

Contoh store nanti:
- cartStore.js
- orderStore.js
- cashierStore.js
- kitchenStore.js

Pada fase 1 cukup buat folder.

---

## Utils

Digunakan untuk helper frontend.

Contoh nanti:
- formatCurrency.js
- formatDate.js
- statusColor.js

Pada fase 1 boleh buat `formatCurrency.js`.

---

# 3. Arsitektur Filament 3

Filament hanya digunakan untuk backoffice/admin.

Route Filament:

```txt
/admin
```

Filament digunakan untuk:
- Dashboard Owner
- Master Menu
- Master Kategori Menu
- Master Bahan Baku
- Master Resep/HPP
- Master Supplier
- Master Meja
- Master Metode Pembayaran
- Master Kategori Pengeluaran
- Pembelian Bahan Baku
- Pencatatan Pengeluaran
- Laporan Penjualan
- Laporan Margin
- Laporan Stok
- User Management
- Role & Permission

Filament tidak digunakan untuk:
- POS kasir
- QR menu customer
- Cart customer
- Checkout customer
- Kitchen display
- Order status customer
- Tampilan pelayan

Modul tersebut dibuat menggunakan Vue 3 + Inertia.

---

# 4. Role dan Permission Dasar

Gunakan Spatie Laravel Permission.

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

Permission awal:

```txt
view_dashboard
manage_users
manage_roles
manage_menus
manage_categories
manage_raw_materials
manage_recipes
manage_suppliers
manage_tables
manage_payment_methods
manage_expenses
manage_purchases
view_reports
access_pos
access_kitchen
process_orders
process_payments
```

Pada fase 1:
- Buat seeder role dan permission.
- Assign semua permission ke `super_admin`.
- Assign permission sesuai role secara dasar.
- Jangan buat logic permission yang terlalu kompleks.

---

# 5. Route Dasar

Siapkan route dasar berikut:

```txt
/admin
/cashier
/kitchen
/customer/menu/{table}
```

Aturan:
- `/admin` digunakan oleh Filament.
- `/cashier` menggunakan Inertia dan middleware auth.
- `/kitchen` menggunakan Inertia dan middleware auth.
- `/customer/menu/{table}` boleh public karena diakses dari QR meja.

---

# 6. Middleware Dasar

Gunakan middleware:
- auth
- role
- permission

Route internal wajib dilindungi auth.

Akses:
- `/admin` untuk super_admin, owner, admin_manager.
- `/cashier` untuk super_admin, owner, cashier.
- `/kitchen` untuk super_admin, owner, kitchen.
- `/customer/menu/{table}` public.

---

# 7. Prinsip Controller

Controller harus tipis.

Alur controller:

```txt
Request
→ FormRequest
→ Controller
→ Service
→ Action/Model
→ Response
```

Controller tidak boleh:
- berisi query kompleks,
- berisi hitungan transaksi panjang,
- berisi logic stok,
- berisi logic laporan.

---

# 8. Prinsip Service Layer

Service digunakan untuk:
- order,
- transaksi,
- inventory,
- payment,
- purchase,
- expense,
- report.

Service tidak boleh mengatur response HTTP.

Service boleh menggunakan:
- model,
- action,
- DTO,
- enum,
- database transaction.

---

# 9. Prinsip Enum

Semua status wajib menggunakan enum.

Jangan gunakan string status manual berulang.

Contoh status yang harus menggunakan enum:
- order status
- payment status
- purchase status
- stock movement type
- table status

---

# 10. Prinsip Naming

Gunakan bahasa Inggris untuk:
- nama folder,
- nama class,
- nama model,
- nama table,
- nama field,
- nama method.

Contoh benar:

```txt
Menu
RawMaterial
PaymentMethod
ExpenseCategory
StockMovement
```

Contoh salah:

```txt
BahanBaku
MetodePembayaran
KategoriPengeluaran
```

---

# 11. Coding Standard

Wajib:
- Controller tipis.
- Gunakan Service Layer.
- Gunakan Action untuk proses spesifik.
- Gunakan Form Request untuk validasi.
- Gunakan API Resource untuk response JSON.
- Gunakan Enum untuk status.
- Gunakan Policy untuk authorization.
- Gunakan SoftDeletes untuk data penting.
- Gunakan database transaction untuk proses transaksi penting.
- Gunakan eager loading untuk menghindari N+1 query.
- Jangan membuat Blade manual untuk admin.
- Jangan mencampur logic Filament dengan logic frontend.
- Jangan membuat fitur di luar scope fase 1.

---

# 12. Output Akhir Fase 1

Setelah fase 1 selesai, project harus memiliki:

- Struktur folder backend rapi.
- Struktur folder frontend rapi.
- Folder Service, Action, DTO, Enum, Policy, Observer, Support, Trait tersedia.
- Role dan permission dasar tersedia.
- Seeder role dan permission tersedia.
- Route dasar tersedia.
- Halaman placeholder Inertia untuk cashier, kitchen, customer menu, dashboard tersedia.
- Filament admin tetap berjalan di `/admin`.
- Project tetap bisa dijalankan dengan `composer run dev`.
- Tidak ada fitur bisnis detail yang dibuat di fase ini.
