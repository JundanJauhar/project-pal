@extends('layouts.app')

@section('title', 'Pengadaan - PT PAL Indonesia')

@section('content')

<style>
    .form-section {
        background: white;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
    }
    .form-section h3 {
        font-weight: 600;
        margin-bottom: 20px;
        color: #000;
        font-size: 18px;
    }
    .form-label {
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
        display: block;
    }
    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }
    .form-control, .form-select {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 10px 12px;
        width: 100%;
    }
    .form-control:focus, .form-select:focus {
        border-color: #003d82;
        box-shadow: 0 0 0 0.2rem rgba(0, 61, 130, 0.25);
        outline: none;
    }
    textarea.form-control {
        resize: vertical;
    }
    .btn-tambah-item {
        background: #003d82;
        border-color: #003d82;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        border: none;
        cursor: pointer;
    }
    .btn-tambah-item:hover {
        background: #0056b3;
    }
    .btn-kirim {
        background: #003d82;
        border-color: #003d82;
        color: white;
        padding: 12px 32px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 16px;
        border: none;
        cursor: pointer;
        margin-top: 20px;
    }
    .btn-kirim:hover {
        background: #0056b3;
    }
    .item-row {
        background: #f8f9fa;
        padding: 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        border: 1px solid #e9ecef;
    }
    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .item-number {
        font-weight: 600;
        color: #003d82;
    }
    .btn-remove-item {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        border: none;
        cursor: pointer;
    }
    .btn-remove-item:hover {
        background: #c82333;
    }
    .priority-select {
        width: 100%;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="font-weight: 600;">Pengadaan</h2>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle-fill"></i> 
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('procurements.store') }}" id="procurementForm">
        @csrf
        
        <div class="row">
            <!-- Informasi Umum Section -->
            <div class="col-md-6">
                <div class="form-section">
                    <h3>Informasi Umum</h3>
                    
                    <div class="mb-3">
                        <label for="request_name" class="form-label required">Judul Pengadaan</label>
                        <input type="text" class="form-control" id="request_name" name="request_name" 
                               value="{{ old('request_name') }}" required placeholder="Masukkan judul pengadaan">
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label required">Prioritas</label>
                        <select class="form-select priority-select" id="priority" name="priority" required>
                            <option value="">Pilih Prioritas</option>
                            <option value="rendah" {{ old('priority') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="sedang" {{ old('priority') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="tinggi" {{ old('priority') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label required">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  required placeholder="Masukkan deskripsi pengadaan">{{ old('description') }}</textarea>
                    </div>

                    <!-- Hidden fields for required data -->
                    @if(isset($defaultProject))
                    <input type="hidden" name="project_id" value="{{ $defaultProject->project_id }}">
                    @elseif(isset($projects) && $projects->count() > 0)
                    <input type="hidden" name="project_id" value="{{ $projects->first()->project_id }}">
                    @endif
                    @if(isset($defaultDivision))
                    <input type="hidden" name="applicant_department" value="{{ $defaultDivision->divisi_id }}">
                    @elseif(Auth::user()->division_id)
                    <input type="hidden" name="applicant_department" value="{{ Auth::user()->division_id }}">
                    @elseif(isset($divisions) && $divisions->count() > 0)
                    <input type="hidden" name="applicant_department" value="{{ $divisions->first()->divisi_id }}">
                    @endif
                    <input type="hidden" name="created_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="deadline_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                </div>
            </div>

            <!-- Daftar Barang Section -->
            <div class="col-md-6">
                <div class="form-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 style="margin: 0;">Daftar Barang</h3>
                        <button type="button" class="btn btn-tambah-item" id="addItemBtn">
                            <i class="bi bi-plus-circle"></i> Tambah Item
                        </button>
                    </div>

                    <div id="itemsContainer">
                        <!-- Items will be added here dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="row mt-4">
            <div class="col-12">
                <button type="submit" class="btn btn-kirim">
                    <i class="bi bi-send"></i> Kirim Pengadaan
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let itemCount = 0;

    document.addEventListener('DOMContentLoaded', function() {
        const addItemBtn = document.getElementById('addItemBtn');
        const itemsContainer = document.getElementById('itemsContainer');

        // Add initial item if none exist
        if (itemsContainer.children.length === 0) {
            addItem();
        }

        if (addItemBtn) {
            addItemBtn.addEventListener('click', function() {
                addItem();
            });
        }

        function addItem() {
            itemCount++;
            const itemHtml = `
                <div class="item-row" data-item-index="${itemCount}">
                    <div class="item-header">
                        <span class="item-number">Item ${itemCount}</span>
                        <button type="button" class="btn btn-remove-item" onclick="removeItem(this)">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label required">Nama Barang</label>
                            <input type="text" class="form-control" name="items[${itemCount}][item_name]" required placeholder="Masukkan nama barang">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Satuan</label>
                            <input type="text" class="form-control" name="items[${itemCount}][unit]" placeholder="pcs, kg, m, dll">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label required">Spesifikasi</label>
                            <textarea class="form-control" name="items[${itemCount}][specification]" rows="3" required placeholder="Masukkan spesifikasi barang"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="items[${itemCount}][unit_price]" min="0" step="0.01" placeholder="0">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Harga Estimasi</label>
                            <input type="number" class="form-control" name="items[${itemCount}][estimated_price]" min="0" step="0.01" placeholder="0">
                        </div>
                    </div>
                </div>
            `;
            itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
        }

        window.removeItem = function(btn) {
            const itemRow = btn.closest('.item-row');
            if (itemRow) {
                itemRow.remove();
                
                // Renumber items
                const items = itemsContainer.querySelectorAll('.item-row');
                items.forEach((item, index) => {
                    const numberSpan = item.querySelector('.item-number');
                    if (numberSpan) {
                        numberSpan.textContent = `Item ${index + 1}`;
                    }
                });
            }
        };

        // Form validation
        const form = document.getElementById('procurementForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const items = itemsContainer.querySelectorAll('.item-row');
                if (items.length === 0) {
                    e.preventDefault();
                    alert('Minimal harus ada 1 item');
                    return false;
                }
            });
        }
    });
</script>
@endpush

@endsection
