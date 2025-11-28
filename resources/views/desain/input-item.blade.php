@extends('layouts.app')

@section('title', 'Form Pengadaan - PT PAL Indonesia')

@push('styles')
<style>
    .page-wrapper {
        background: #ffffff;
        min-height: 100vh;
        padding: 40px 60px;
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 30px;
        color: #000;
    }

    .form-box {
        background: #F5F5F5;
        border-radius: 8px;
        border: none;
        padding: 12px 16px;
        width: 100%;
        font-size: 14px;
        color: #333;
    }

    .form-box:focus {
        outline: none;
        background: #ECECEC;
    }

    .form-box:disabled,
    .form-box[readonly] {
        background: #F5F5F5;
        color: #666;
        cursor: not-allowed;
    }

    textarea.form-box {
        resize: vertical;
        min-height: 100px;
    }

    .label-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        color: #000;
        display: block;
    }

    .btn-tambah-item {
        background: #004A99;
        color: white;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-tambah-item:hover {
        background: #003875;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 74, 153, 0.3);
    }

    .btn-hapus-item {
        background: #dc3545;
        color: white;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s ease;
    }

    .btn-hapus-item:hover {
        background: #c82333;
    }

    .btn-kirim {
        background: #004A99;
        color: white;
        font-weight: 600;
        padding: 14px 40px;
        border-radius: 8px;
        border: none;
        margin-top: 40px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-kirim:hover {
        background: #003875;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 74, 153, 0.4);
    }

    .form-section {
        margin-bottom: 25px;
    }

    .item-row {
        background: #FAFAFA;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        position: relative;
        transition: all 0.3s ease;
    }

    .item-row:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .item-number {
        position: absolute;
        top: -12px;
        left: 20px;
        background: #004A99;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .divider {
        border-bottom: 2px solid #E0E0E0;
        margin: 40px 0;
    }

    .info-section {
        background: #F9F9F9;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 40px;
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 20px;
        }

        .btn-kirim {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="page-wrapper">
    <form action="{{ route('desain.kirim-pengadaan',$projects->first()->project_id) }}" method="POST" id="pengadaanForm">
        @csrf
<!-- 
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">Tambah Pengadaan</div>

            @if(Auth::check() && Auth::user()->roles === 'supply_chain')
            <button type="button" class="btn-tambah-item" id="addItemBtn">
                <i class="bi bi-plus-circle me-2"></i>Tambah Item
            </button>
            @endif
        </div> -->
        <div id="itemContainer">

            <div class="item-row" data-item-index="1">
                <!-- <span class="item-number">Item 1</span> -->

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-section">
                            <label class="label-title">Nama Barang *</label>
                            <input type="text" class="form-box" name="nama_barang[]" required placeholder="Masukkan nama barang...">
                        </div>

                        <div class="form-section">
                            <label class="label-title">Spesifikasi *</label>
                            <textarea class="form-box" name="spesifikasi[]" required placeholder="Masukkan spesifikasi barang..."></textarea>
                        </div>

                        <div class="form-section">
                            <label class="label-title">Satuan *</label>
                            <input type="text" class="form-box" name="satuan[]" required placeholder="Contoh: Unit, Kg, Liter...">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-section">
                            <label class="label-title">Harga *</label>
                            <input type="number" class="form-box" name="harga[]" required placeholder="Masukkan harga..." min="0">
                        </div>

                        <div class="form-section">
                            <label class="label-title">Harga Estimasi *</label>
                            <input type="number" class="form-box" name="harga_estimasi[]" required placeholder="Masukkan estimasi harga..." min="0">
                        </div>

                        <div class="form-section">
                            <button type="button" class="btn-hapus-item" onclick="removeItem(this)">
                                <i class="bi bi-trash me-1"></i>Hapus Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="text-start">
            <button type="submit" class="btn-kirim">
                <i class="bi bi-send me-2"></i>Kirim Pengadaan
            </button>
        </div>
    </form>

</div>

@endsection

@push('scripts')
<script>
    let itemCounter = 1; // langsung mulai dari 1 karena sudah muncul

    addItemBtn.addEventListener('click', function() {
        itemCounter++;

        const html = `
    <div class="item-row" data-item-index="${itemCounter}">
        <span class="item-number">Item ${itemCounter}</span>
        ...
        (form input sama seperti di atas)
    </div>
    `;

        document.getElementById('itemContainer').insertAdjacentHTML('beforeend', html);
        updateItemNumbers();
    });

    const addItemBtn = document.getElementById('addItemBtn');

    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            itemCounter++;
            const container = document.getElementById('itemContainer');

            // Remove alert if exists
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }

            const html = `
            <div class="item-row" data-item-index="${itemCounter}">
                <span class="item-number">Item ${itemCounter}</span>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-section">
                            <label class="label-title">Nama Barang *</label>
                            <input type="text" class="form-box" name="nama_barang[]" required placeholder="Masukkan nama barang...">
                        </div>

                        <div class="form-section">
                            <label class="label-title">Spesifikasi *</label>
                            <textarea class="form-box" name="spesifikasi[]" required placeholder="Masukkan spesifikasi barang..."></textarea>
                        </div>

                        <div class="form-section">
                            <label class="label-title">Satuan *</label>
                            <input type="text" class="form-box" name="satuan[]" required placeholder="Contoh: Unit, Kg, Liter...">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-section">
                            <label class="label-title">Harga *</label>
                            <input type="number" class="form-box" name="harga[]" required placeholder="Masukkan harga..." min="0">
                        </div>

                        <div class="form-section">
                            <label class="label-title">Harga Estimasi *</label>
                            <input type="number" class="form-box" name="harga_estimasi[]" required placeholder="Masukkan estimasi harga..." min="0">
                        </div>

                        <div class="form-section">
                            <button type="button" class="btn-hapus-item" onclick="removeItem(this)">
                                <i class="bi bi-trash me-1"></i>Hapus Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
            updateItemNumbers();
        });
    }

    function removeItem(button) {
        if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            button.closest('.item-row').remove();
            updateItemNumbers();

            // Show alert if no items
            const container = document.getElementById('itemContainer');
            const items = container.querySelectorAll('.item-row');
            if (items.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Belum ada item. Klik "Tambah Item" untuk menambahkan barang.
                    </div>
                `;
            }
        }
    }

    function updateItemNumbers() {
        const items = document.querySelectorAll('.item-row');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.item-number');
            if (numberSpan) {
                numberSpan.textContent = `Item ${index + 1}`;
            }
            item.setAttribute('data-item-index', index);
        });
        itemCounter = items.length;
    }

    const form = document.getElementById('pengadaanForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const items = document.querySelectorAll('.item-row');

            if (items.length === 0) {
                e.preventDefault();
                alert('Mohon tambahkan minimal 1 item sebelum mengirim pengadaan!');
                return false;
            }

            if (!confirm('Apakah Anda yakin ingin mengirim pengadaan ini?')) {
                e.preventDefault();
                return false;
            }
        });
    }
</script>
@endpush