@extends('layouts.app', ['hideNavbar' => true])

@section('title', 'Tambah Pengadaan - PT PAL Indonesia')

@push('styles')
<style>
    .pengadaan-section {
        border: 2px solid #003d82;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background: #f8f9fa;
    }

    .section-title {
        color: #003d82;
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 15px;
        text-decoration: underline;
    }

    .btn-tambah {
        background: #003d82;
        color: white;
        font-weight: 600;
        padding: 12px 40px;
        border: none;
    }

    .btn-tambah:hover {
        background: #002d5c;
        color: white;
    }

    .btn-kirim {
        background: #003d82;
        color: white;
        font-weight: 600;
        padding: 12px 60px;
        border: none;
    }

    .btn-kirim:hover {
        background: #002d5c;
        color: white;
    }

    .bi-x-circle {
        font-size: 24px;
        color: #dc3545;
        cursor: pointer;
    }

    .logo {
        height: 220px;
        object-fit: contain;
    }

    .header-logo-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: -80px;
        margin-bottom: 15px;
    }

    .close-btn {
        position: absolute;
        right: 90px;
        top: 110px;
        font-size: 28px;
        color: #DA3B3B;
        cursor: pointer;
    }

    .close-btn:hover {
        opacity: 0.7;
    }

    .form-label {
        font-weight: 500;
        color: #333;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
    }

    .form-control:focus, .form-select:focus {
        border-color: #003d82;
        box-shadow: 0 0 0 0.2rem rgba(0, 61, 130, 0.25);
    }
</style>
@endpush

@section('content')
<div class="header-logo-wrapper">
    <img src="{{ asset('images/logo-pal.png') }}" alt="Logo PAL" class="logo">
</div>
<a href="{{ route('supply-chain.dashboard') }}" class="close-btn" wire:navigate>
    <i class="bi bi-x-circle"></i>
</a>

<div class="container px-5 pb-5">
    <div class="mb-4">
        <h3 class="mb-0">Tambah Pengadaan</h3>
    </div>

    <form id="formTambahPengadaan" action="{{ route('supply-chain.dashboard.store') }}" method="POST">
        @csrf
        <div id="pengadaanContainer">
            <!-- Pengadaan 1 (Default) -->
            <div class="pengadaan-section" data-index="1">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title mb-0">Pengadaan 1</h6>
                    <button type="button" class="btn btn-sm btn-danger btn-remove-pengadaan" style="display: none;">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Nama Pengadaan <span class="text-danger">*</span></label>
                        <input type="text" name="pengadaan[1][name]" class="form-control" placeholder="Pengadaan Material baja berkualitas tinggi" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="pengadaan[1][start_date]" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Waktu <span class="text-danger">*</span></label>
                        <input type="date" name="pengadaan[1][end_date]" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select name="pengadaan[1][department]" class="form-select" required>
                            <option value="">Pilih Department</option>
                            @foreach(\App\Models\Department::all() as $dept)
                            <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                        <select name="pengadaan[1][priority]" class="form-select" required>
                            <option value="">Pilih Prioritas</option>
                            <option value="rendah">RENDAH ⊗</option>
                            <option value="sedang">SEDANG ⊗</option>
                            <option value="tinggi">TINGGI ⊗</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Tambah Section -->
        <div class="text-center mb-4">
            <button type="button" class="btn btn-tambah" id="btnTambahSection">
                <i class="bi bi-plus-circle"></i> Tambah
            </button>
        </div>

        <!-- Button Kirim -->
        <div class="text-end">
            <button type="submit" class="btn btn-kirim">
                Kirim Pengadaan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let pengadaanIndex = 1;

    // Tambah Section Pengadaan
    document.getElementById('btnTambahSection').addEventListener('click', function() {
        pengadaanIndex++;
        const container = document.getElementById('pengadaanContainer');

        const newSection = document.createElement('div');
        newSection.className = 'pengadaan-section';
        newSection.setAttribute('data-index', pengadaanIndex);
        newSection.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="section-title mb-0">Pengadaan ${pengadaanIndex}</h6>
                <button type="button" class="btn btn-sm btn-danger btn-remove-pengadaan">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </div>

            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nama Pengadaan <span class="text-danger">*</span></label>
                    <input type="text" name="pengadaan[${pengadaanIndex}][name]" class="form-control" placeholder="Pengadaan Material baja berkualitas tinggi" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="pengadaan[${pengadaanIndex}][start_date]" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Waktu <span class="text-danger">*</span></label>
                    <input type="date" name="pengadaan[${pengadaanIndex}][end_date]" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Department <span class="text-danger">*</span></label>
                    <select name="pengadaan[${pengadaanIndex}][department]" class="form-select" required>
                        <option value="">Pilih Department</option>
                        @foreach(\App\Models\Department::all() as $dept)
                        <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                    <select name="pengadaan[${pengadaanIndex}][priority]" class="form-select" required>
                        <option value="">Pilih Prioritas</option>
                        <option value="rendah">RENDAH ⊗</option>
                        <option value="sedang">SEDANG ⊗</option>
                        <option value="tinggi">TINGGI ⊗</option>
                    </select>
                </div>
            </div>
        `;

        container.appendChild(newSection);
        updateRemoveButtons();
    });

    // Remove Section Pengadaan
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-pengadaan')) {
            e.target.closest('.pengadaan-section').remove();
            updateRemoveButtons();
            renumberSections();
        }
    });

    function updateRemoveButtons() {
        const sections = document.querySelectorAll('.pengadaan-section');
        sections.forEach((section, index) => {
            const removeBtn = section.querySelector('.btn-remove-pengadaan');
            if (removeBtn) {
                removeBtn.style.display = sections.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    function renumberSections() {
        const sections = document.querySelectorAll('.pengadaan-section');
        sections.forEach((section, index) => {
            const title = section.querySelector('.section-title');
            if (title) {
                title.textContent = `Pengadaan ${index + 1}`;
            }
        });
    }
</script>
@endpush
