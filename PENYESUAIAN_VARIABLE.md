# Dokumentasi Penyesuaian Variable dengan Migration, Seeder, dan Model

## Ringkasan Perubahan

Semua variable di seeder, model, dan relationship telah disesuaikan dengan struktur database migration yang baru. Berikut adalah detail perubahan yang telah dilakukan:

---

## 1. Division Model & Seeder

### Perubahan Model (`app/Models/Division.php`)

-   **Primary Key**: `divisi_id` → `division_id`
-   **Fillable**: `'name'` → `'division_name'`
-   **Foreign Key di relationships**: `divisi_id` → `division_id`

### Perubahan Relationships

Semua relationship di Division diperbarui untuk menggunakan `division_id`:

```php
// Before
public function users(): HasMany {
    return $this->hasMany(User::class, 'division_id', 'divisi_id');
}

// After
public function users(): HasMany {
    return $this->hasMany(User::class, 'division_id', 'division_id');
}
```

### Seeder

DivisionSeeder sudah menggunakan `division_name` yang sesuai dengan migration.

---

## 2. Checkpoint Model & Seeder

### Perubahan Seeder (`database/seeders/CheckpointSeeder.php`)

-   **Removed**: `'is_true' => false` (field tidak ada di migration)
-   **Kept**: `'is_final' => false/true` (sesuai migration)

### Perubahan Model Relationships

```php
// Before
public function responsibleDivision(): BelongsTo {
    return $this->belongsTo(Division::class, 'responsible_division', 'divisi_id');
}

// After
public function responsibleDivision(): BelongsTo {
    return $this->belongsTo(Division::class, 'responsible_division', 'division_id');
}
```

---

## 3. Item Model

### Perubahan Fillable (`app/Models/Item.php`)

-   **Added**: `'item_description'` (sesuai migration column name)

```php
protected $fillable = [
    'request_procurement_id',
    'item_name',
    'item_description',  // ← Added
    'specification',
    'amount',
    'unit',
    'unit_price',
    'total_price',
];
```

---

## 4. Contract Model

### Perubahan Fillable (`app/Models/Contract.php`)

Ditambahkan column yang ada di migration:

```php
protected $fillable = [
    'project_id',
    'vendor_id',
    'contract_number',
    'contract_value',      // ← Added
    'start_date',          // ← Added
    'end_date',            // ← Added
    'status',
    'created_by',          // ← Added
];
```

### Perubahan Relationships

Ditambahkan relationship untuk user creator:

```php
public function creator(): BelongsTo {
    return $this->belongsTo(User::class, 'created_by', 'user_id');
}
```

---

## 5. Project Model

### Fillable

Sudah benar dengan migration:

```php
protected $fillable = [
    'project_code',
    'project_name',
    'description',
    'owner_division_id',
    'priority',
    'start_date',
    'end_date',
    'status_project',
    'review_notes',
    'review_documents',
];
```

### Relationships

Relationship ke Division sudah benar dengan `division_id`.

---

## 6. Request Procurement Model

### Perubahan Relationships

```php
// Fixed
public function applicantDivision(): BelongsTo {
    return $this->belongsTo(Division::class, 'applicant_department', 'division_id');
}
```

---

## 7. User Foreign Key References di Semua Models

Semua relationship ke User model diperbarui untuk menggunakan `'user_id'` sebagai target key:

### Models yang diperbarui:

1. **InspectionReport** - `inspector()`: `'inspector_id', 'user_id'`
2. **Evatek** - `evaluator()`: `'evaluated_by', 'user_id'`
3. **Hps** - `creator()`: `'created_by', 'user_id'`
4. **PaymentSchedule** - `accountingVerifier()`, `treasuryVerifier()`: `'verified_by_*', 'user_id'`
5. **ProcurementProgress** - `user()`: `'user_id', 'user_id'`
6. **Approval** - `approver()`: `'approver_id', 'user_id'`
7. **Notification** - `user()`, `sender()`: `'user_id', 'user_id'` dan `'sender_id', 'user_id'`
8. **NcrReport** - `creator()`, `verifier()`, `assignedUser()`: semua updated

---

## 8. Summary of Database Structure

| Table                | Primary Key         | Notable Columns                                                                                       |
| -------------------- | ------------------- | ----------------------------------------------------------------------------------------------------- |
| divisions            | division_id         | division_name                                                                                         |
| users                | user_id             | division_id (FK)                                                                                      |
| projects             | project_id          | project_code, project_name, owner_division_id (FK)                                                    |
| checkpoints          | point_id            | responsible_division (FK to division_id)                                                              |
| request_procurement  | request_id          | procurement_id (FK), vendor_id (FK), department_id (FK)                                               |
| items                | item_id             | request_procurement_id (FK), item_description                                                         |
| contracts            | contract_id         | project_id (FK), vendor_id (FK), created_by (FK to user_id)                                           |
| payment_schedules    | payment_schedule_id | project_id (FK), contract_id (FK), verified_by_accounting/treasury (FK to user_id)                    |
| inspection_reports   | inspection_id       | project_id (FK), item_id (FK), inspector_id (FK to user_id)                                           |
| ncr_reports          | ncr_id              | inspection_id (FK), project_id (FK), item_id (FK), created_by/verified_by/assigned_to (FK to user_id) |
| vendors              | id_vendor           | -                                                                                                     |
| procurement          | procurement_id      | department_procurement (FK)                                                                           |
| evatek               | evatek_id           | project_id (FK), evaluated_by (FK to user_id)                                                         |
| hps                  | hps_id              | project_id (FK), created_by (FK to user_id)                                                           |
| negotiations         | negotiation_id      | request_id (FK)                                                                                       |
| procurement_progress | progress_id         | permintaan_pengadaan_id (FK to request_id), titik_id (FK to point_id), user_id (FK)                   |
| notifications        | notification_id     | user_id (FK), sender_id (FK to user_id)                                                               |
| approvals            | approval_id         | approver_id (FK to user_id)                                                                           |

---

## 9. Catatan Penting

1. **Division ID**: Primary Key di `divisions` table adalah `division_id`, bukan `divisi_id`
2. **User ID**: Primary Key di `users` table adalah `user_id`, semua FK relationships harus menggunakan ini
3. **Item Description**: Column di `items` table adalah `item_description`, bukan `item_desc` atau yang lain
4. **Vendor ID**: Primary Key di `vendors` table adalah `id_vendor`
5. **Timestamps**: Beberapa model menggunakan `public $timestamps = false` sesuai kebutuhan

---

## 10. Testing Recommendations

Setelah penyesuaian ini, disarankan untuk:

1. Menjalankan migrations: `php artisan migrate:fresh --seed`
2. Test semua relationships di tinker console
3. Verify semua CRUD operations bekerja dengan benar
4. Check foreign key constraints di database

---

## Status: ✅ SELESAI

Semua variable telah disesuaikan dan konsisten dengan struktur migration dan model yang baru.
