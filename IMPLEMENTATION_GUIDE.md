# PT PAL Indonesia - Procurement System Implementation Guide

## Overview
Sistem procurement management untuk PT PAL Indonesia yang mengelola 16 tahapan proses pengadaan dengan berbagai role user.

## Tech Stack
- **Backend**: Laravel 11
- **Frontend**: Blade Templates + Bootstrap 5
- **Database**: MySQL

## User Roles
1. **User** - User dari divisi yang mengajukan pengadaan
2. **Supply Chain (SC)** - Mengelola procurement, vendor, negosiasi
3. **Treasury** - Pembayaran LC/TT/Sekbun
4. **Accounting** - Verifikasi dokumen pembayaran
5. **Quality Assurance (QA)** - Inspeksi barang dan NCR
6. **Sekretaris Direksi** - Approval kontrak
7. **Desain** - Update HPS dan Evatek

## 16 Tahapan Procurement
1. PDM (Permintaan Departemen Material)
2. Pengecekan
3. Penawaran Permintaan
4. Evatek (Evaluasi Teknis)
5. Negosiasi (ada riset HPS)
6. Usulan Pengadaan / OC
7. Pengesahan Kontrak (review, tanda tangan direksi)
8. Pengiriman Material (impor/lokal)
9. Pembayaran DP
10. Proses Importasi / Produksi Material
11. Kedatangan Material
12. Serah Terima Dokumen
13. Inspeksi Barang
14. Berita Acara / NCR (jika gagal)
15. Verifikasi Dokumen
16. Pembayaran

## Database Structure

### Core Tables
- `users` - User accounts dengan role
- `divisions` - Divisi dalam perusahaan
- `projects` - Proyek pengadaan
- `checkpoints` - 16 tahapan procurement

### Procurement Tables
- `request_procurement` - Permintaan pengadaan
- `items` - Item yang diminta
- `vendors` - Daftar vendor
- `contracts` - Kontrak dengan vendor
- `hps` - Harga Perkiraan Sendiri
- `evatek` - Evaluasi teknis
- `negotiations` - Negosiasi harga
- `approvals` - Approval workflow

### Operational Tables
- `procurement_progress` - Progress tracking
- `inspection_reports` - Laporan inspeksi QA
- `ncr_reports` - Non-Conformance Reports
- `payment_schedules` - Jadwal pembayaran
- `notifications` - System notifications

## Controllers Structure

### DashboardController
- Dashboard overview dengan statistik
- Filtering by division
- Timeline procurement

### ProjectController
- CRUD projects
- Update status
- Search projects

### SupplyChainController
- Review project
- Material requests management
- Vendor selection
- Negotiations
- Material shipping
- Request HPS update

### PaymentController
- Payment schedules
- Accounting verification
- Treasury verification
- Open LC/TT/Sekbun

### InspectionController
- Inspection reports
- NCR management
- QA verification

### NotificationController
- User notifications
- Mark as read
- Real-time updates

## Key Features

### 1. Notification System
Otomatis mengirim notifikasi ke role yang relevan pada setiap perubahan status:
- Project baru → Supply Chain
- Approval needed → Sekretaris Direksi
- Payment verification → Treasury/Accounting
- Inspection required → QA
- NCR created → Supply Chain
- HPS update needed → Desain

### 2. Workflow Automation
- Status otomatis berubah sesuai approval
- Progress tracking untuk setiap checkpoint
- Conditional routing berdasarkan material type (import/lokal)

### 3. Document Management
- Upload attachment untuk:
  - Inspection reports (Berita Acara)
  - Payment documents
  - NCR reports
  - Contracts

### 4. Import vs Local Material
- DP payment untuk import
- Sekbun untuk import items
- LC/TT untuk pembayaran
- Different workflow berdasarkan material type

## Installation Steps

### 1. Clone & Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration
Update `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_pal
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed --class=DivisionSeeder
php artisan db:seed --class=CheckpointSeeder
php artisan db:seed --class=UserSeeder
```

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Run Application
```bash
php artisan serve
npm run dev
```

## Default User Credentials
```
User Division:
Email: user@pal.com
Password: password

Supply Chain:
Email: supplychain@pal.com
Password: password

Treasury:
Email: treasury@pal.com
Password: password

Accounting:
Email: accounting@pal.com
Password: password

Quality Assurance:
Email: qa@pal.com
Password: password

Sekretaris Direksi:
Email: sekretaris@pal.com
Password: password

Desain:
Email: desain@pal.com
Password: password
```

## API Endpoints

### Dashboard
- `GET /dashboard` - Main dashboard
- `GET /dashboard/division/{divisionId}` - Division dashboard
- `GET /dashboard/statistics` - Statistics data
- `GET /dashboard/timeline/{projectId}` - Procurement timeline

### Projects
- `GET /projects` - List projects
- `POST /projects` - Create project
- `GET /projects/{id}` - Show project
- `PUT /projects/{id}` - Update project
- `POST /projects/{id}/status` - Update status
- `DELETE /projects/{id}` - Delete project

### Supply Chain
- `GET /supply-chain/dashboard`
- `GET /supply-chain/projects/{id}/review`
- `POST /supply-chain/projects/{id}/approve`
- `GET /supply-chain/material-requests`
- `POST /supply-chain/projects/{id}/select-vendor`
- `POST /supply-chain/projects/{id}/negotiation`
- `POST /supply-chain/projects/{id}/material-arrival`

### Payments
- `GET /payments`
- `POST /payments`
- `GET /payments/{id}`
- `POST /payments/{id}/accounting-verification`
- `POST /payments/{id}/treasury-verification`
- `POST /payments/projects/{id}/open-lc-tt`
- `POST /payments/projects/{id}/open-sekbun`

### Inspections
- `GET /inspections`
- `POST /inspections`
- `GET /inspections/{id}`
- `GET /inspections/ncr`
- `POST /inspections/ncr/{id}/verify`

## User Flows by Role

### User Flow (Divisi)
1. Login
2. View Dashboard Utama
3. View Dashboard Divisi
4. Pilih project yang akan dilakukan pengadaan
5. Update keternotifikasi
6. Lihat progress

### Desain Flow
1. Login
2. View Dashboard
3. Lihat project yang diinginkan
4. Update Penyusunan HPS
5. Input list project/POM
6. Update Evatek
7. Pilih Evatek
8. Update Approval/Reject Evatek
9. Notify SC

### Supply Chain Flow
1. Login
2. View Dashboard Divisi
3. Lihat list permintaan pembelian
4. Update permintaan pembelian
5. Update Nego-klasi
6. Nego > HPS? → Request HPS update
7. Update pengadaan kontrak
8. Pengiriman material
9. Material Import? → Notify Accounting
10. Update kedatangan material

### Treasury Flow
1. Login
2. View Dashboard Divisi
3. Lihat project yang diinginkan
4. Lihat status barang
5. Barang Import? 
   - Yes: Update Open Sekbun
   - No: Update Open LC/TT

### Accounting Flow
1. Login
2. View Dashboard Divisi
3. Lihat project yang diinginkan
4. Lihat status barang
5. Barang Import?
   - Yes: Update verifikasi DP → Notify Treasury untuk open LC/TT
   - No: Update verifikasi dokumen → Notify Treasury untuk open Sekbun

### Quality Assurance Flow
1. Login
2. View Dashboard Divisi
3. Lihat project yang diinginkan
4. Update bahwa barang sudah sampai
5. Update Inspeksi
6. Hasil Inspeksi?
   - Pass: Update Berita Acara → Notify Accounting
   - Fail: Update NCR → Notify SC

### Sekretaris Direksi Flow
1. Login
2. View Dashboard Divisi
3. Lihat project yang diinginkan
4. Update memverifikasi kontrak
5. Notify SC untuk pengiriman material

## Next Steps

### Frontend Development
- [ ] Lengkapi views untuk semua controller
- [ ] Tambah Javascript untuk AJAX operations
- [ ] Implement real-time notifications
- [ ] Add data tables dengan filtering/sorting
- [ ] Create responsive mobile views

### Authentication
- [ ] Install Laravel Breeze/Jetstream
- [ ] Implement role-based middleware
- [ ] Add permission system

### Additional Features
- [ ] Email notifications
- [ ] File upload validation
- [ ] Export reports (PDF/Excel)
- [ ] Dashboard charts/graphs
- [ ] Audit trail/logs
- [ ] Multi-language support

### Testing
- [ ] Unit tests untuk Models
- [ ] Feature tests untuk Controllers
- [ ] Browser tests untuk UI flows

## File Structure
```
app/
├── Http/
│   └── Controllers/
│       ├── DashboardController.php
│       ├── ProjectController.php
│       ├── SupplyChainController.php
│       ├── TreasuryController.php
│       ├── AccountingController.php
│       ├── InspectionController.php
│       ├── PaymentController.php
│       └── NotificationController.php
├── Models/
│   ├── User.php
│   ├── Division.php
│   ├── Project.php
│   ├── Checkpoint.php
│   ├── RequestProcurement.php
│   ├── Item.php
│   ├── Vendor.php
│   ├── Contract.php
│   ├── Hps.php
│   ├── Evatek.php
│   ├── Negotiation.php
│   ├── Approval.php
│   ├── ProcurementProgress.php
│   ├── InspectionReport.php
│   ├── NcrReport.php
│   ├── PaymentSchedule.php
│   └── Notification.php
database/
├── migrations/
│   ├── *_create_users_table.php
│   ├── *_create_divisions_table.php
│   ├── *_create_projects_table.php
│   ├── *_create_checkpoints_table.php
│   ├── *_create_vendors_table.php
│   ├── *_create_contracts_table.php
│   ├── *_create_hps_table.php
│   ├── *_create_evatek_table.php
│   ├── *_create_negotiations_table.php
│   ├── *_create_approvals_table.php
│   ├── *_create_procurement_progress_table.php
│   ├── *_create_request_procurement_table.php
│   ├── *_create_items_table.php
│   ├── *_create_notifications_table.php
│   ├── *_create_inspection_reports_table.php
│   ├── *_create_ncr_reports_table.php
│   └── *_create_payment_schedules_table.php
└── seeders/
    ├── DivisionSeeder.php
    ├── CheckpointSeeder.php
    └── UserSeeder.php
resources/
└── views/
    ├── dashboard/
    │   └── index.blade.php
    ├── projects/
    ├── supply_chain/
    ├── payments/
    ├── inspections/
    └── notifications/
routes/
└── web.php
```

## Support
Untuk pertanyaan atau issue, silakan hubungi tim development.

---
**PT PAL Indonesia** - Procurement Management System
Version 1.0.0
