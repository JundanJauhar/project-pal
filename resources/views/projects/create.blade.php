@extends('layouts.app')

@section('title', 'Pengadaan Baru')

@php
    $defaultDivision = old('owner_division_id', Auth::user()->division_id ?? ($divisions->first()->divisi_id ?? ''));
    $defaultEndDate = old('end_date', \Carbon\Carbon::now()->addWeeks(2)->format('Y-m-d'));
@endphp

@section('content')
<style>
    .procurement-wrapper {
        background: #f7f7f7;
        padding: 32px 40px;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    .procurement-section-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 24px;
    }
    .panel-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .field-group {
        margin-bottom: 20px;
    }
    .field-group label {
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
        font-size: 15px;
    }
    .field-group input,
    .field-group textarea,
    .field-group select {
        background: #efefef;
        border: none;
        border-radius: 14px;
        padding: 14px 18px;
        width: 100%;
        font-size: 15px;
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.08);
    }
    .field-group textarea {
        min-height: 160px;
        resize: vertical;
    }
    .items-wrapper {
        background: transparent;
        border-radius: 18px;
        padding: 0;
    }
    .items-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .btn-add-item,
    .btn-submit-procurement {
        background: #0d3f96;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .btn-add-item:hover,
    .btn-submit-procurement:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(13, 63, 150, 0.35);
    }
    .items-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
    .items-grid .wide {
        grid-column: span 2;
    }
    .items-list {
        margin-top: 24px;
    }
    .items-list-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px 18px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }
    .items-list-card h6 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 6px 0;
    }
    .items-list-card p {
        margin: 0;
        font-size: 14px;
        color: #555;
    }
    .items-remove {
        background: transparent;
        border: none;
        color: #c0392b;
        font-weight: 600;
        cursor: pointer;
    }
    @media (max-width: 992px) {
        .items-grid {
            grid-template-columns: 1fr;
        }
        .items-grid .wide {
            grid-column: span 1;
        }
    }
</style>

<form method="POST" action="{{ route('projects.store') }}" id="procurement-form">
    @csrf
    <input type="hidden" name="code_project" id="code_project" value="{{ old('code_project') }}">
    <input type="hidden" name="owner_division_id" value="{{ $defaultDivision }}">
    <input type="hidden" name="end_date" id="end_date" value="{{ $defaultEndDate }}">

    <div class="procurement-wrapper">
        <div class="row g-5">
            <div class="col-lg-6">
                <h2 class="panel-title">Informasi Umum</h2>

                <div class="field-group">
                    <label for="name_project">Judul Pengadaan *</label>
                    <input type="text"
                           id="name_project"
                           name="name_project"
                           placeholder="Masukkan judul pengadaan"
                           value="{{ old('name_project') }}"
                           required>
                    @error('name_project')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="field-group">
                    <label for="priority">Prioritas *</label>
                    <select id="priority" name="priority" required>
                        <option value="" disabled {{ old('priority') ? '' : 'selected' }}>Pilih prioritas</option>
                        <option value="rendah" {{ old('priority') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                        <option value="sedang" {{ old('priority') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="tinggi" {{ old('priority') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    </select>
                    @error('priority')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="field-group">
                    <label for="description">Deskripsi *</label>
                    <textarea id="description"
                              name="description"
                              placeholder="Tuliskan deskripsi pengadaan secara singkat"
                              required>{{ old('description') }}</textarea>
                    @error('description')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="col-lg-6">
                <div class="items-wrapper">
                    <div class="items-toolbar">
                        <h2 class="panel-title" style="margin:0;">Daftar Barang</h2>
                        <button type="button" class="btn-add-item" id="add-item-btn">Tambah Item</button>
                    </div>

                    <div class="items-grid">
                        <div class="field-group">
                            <label for="item_name">Nama Barang *</label>
                            <input type="text" id="item_name" placeholder="Masukkan nama barang">
                        </div>
                        <div class="field-group">
                            <label for="item_unit">Satuan</label>
                            <input type="text" id="item_unit" placeholder="Misal: unit, pcs, set">
                        </div>
                        <div class="field-group wide">
                            <label for="item_spec">Spesifikasi *</label>
                            <textarea id="item_spec" placeholder="Tuliskan spesifikasi barang"></textarea>
                        </div>
                        <div class="field-group">
                            <label for="item_price">Harga</label>
                            <input type="number" id="item_price" placeholder="0">
                        </div>
                        <div class="field-group">
                            <label for="item_estimation">Harga Estimasi</label>
                            <input type="number" id="item_estimation" placeholder="0">
                        </div>
                    </div>

                    <div class="items-list" id="items-list"></div>
                </div>
            </div>
        </div>

        <div class="mt-5 d-flex justify-content-start">
            <button type="submit" class="btn-submit-procurement">Kirim Pengadaan</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const codeInput = document.getElementById('code_project');
        if (!codeInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const random = String(Math.floor(Math.random() * 1000)).padStart(3, '0');
            codeInput.value = `KCJ-${year}${month}-DES${random}`;
        }

        const endDateInput = document.getElementById('end_date');
        const minEndDate = new Date();
        minEndDate.setDate(minEndDate.getDate() + 14);
        endDateInput.value = minEndDate.toISOString().slice(0, 10);

        const itemsList = document.getElementById('items-list');
        const addItemBtn = document.getElementById('add-item-btn');
        const form = document.getElementById('procurement-form');
        let itemIndex = 0;

        addItemBtn.addEventListener('click', function () {
            const name = document.getElementById('item_name').value.trim();
            const unit = document.getElementById('item_unit').value.trim();
            const spec = document.getElementById('item_spec').value.trim();
            const price = document.getElementById('item_price').value.trim();
            const estimation = document.getElementById('item_estimation').value.trim();

            if (!name || !spec) {
                alert('Nama barang dan spesifikasi wajib diisi.');
                return;
            }

            const card = document.createElement('div');
            card.className = 'items-list-card';
            card.innerHTML = `
                <div>
                    <h6>${name}</h6>
                    <p><strong>Spesifikasi:</strong> ${spec}</p>
                    <p><strong>Satuan:</strong> ${unit || '-'} | <strong>Harga:</strong> ${price || '-'} | <strong>Estimasi:</strong> ${estimation || '-'}</p>
                </div>
                <button type="button" class="items-remove">Hapus</button>
                <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                <input type="hidden" name="items[${itemIndex}][unit]" value="${unit}">
                <input type="hidden" name="items[${itemIndex}][spec]" value="${spec}">
                <input type="hidden" name="items[${itemIndex}][price]" value="${price}">
                <input type="hidden" name="items[${itemIndex}][estimation]" value="${estimation}">
            `;

            card.querySelector('.items-remove').addEventListener('click', () => {
                card.remove();
            });

            itemsList.appendChild(card);
            itemIndex++;

            document.getElementById('item_name').value = '';
            document.getElementById('item_unit').value = '';
            document.getElementById('item_spec').value = '';
            document.getElementById('item_price').value = '';
            document.getElementById('item_estimation').value = '';
        });
    });
</script>
@endpush
