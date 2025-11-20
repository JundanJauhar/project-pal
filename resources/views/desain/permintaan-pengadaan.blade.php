@extends('layouts.app')

@section('title', 'Form Pengadaan - PT PAL Indonesia')

@push('styles')
<style>
    .section-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 30px;
    }

    .form-box {
        background: #F5F5F5;
        border-radius: 12px;
        border: none;
        padding: 12px 16px;
        width: 100%;
        font-size: 15px;
    }

    textarea.form-box {
        resize: none;
        min-height: 140px;
    }

    .label-title {
        font-weight: 600;
        margin-bottom: 6px;
    }

    .btn-item {
        background: #004A99;
        color: white;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 8px;
        border: none;
        float: right;
    }

    .btn-kirim {
        background: #004A99;
        color: white;
        font-weight: 600;
        padding: 12px 26px;
        border-radius: 10px;
        border: none;
        margin-top: 40px;
    }

    .form-section {
        margin-bottom: 40px;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-5">

    <!-- Informasi Umum -->
    <div class="section-title">Informasi Umum</div>

    <div class="row mb-5">

        <!-- Left Column -->
        <div class="col-md-6">

            <div class="form-section">
                <label class="label-title">Nama Project *</label>
                <input type="text" class="form-box" value="{{ $project->code_project ?? '' }}" readonly>
            </div>

            <div class="form-section">
                <label class="label-title">Department *</label>
                <input type="text" class="form-box" value="{{ $project->ownerDivision->nama_divisi ?? '' }}" readonly>
            </div>

            <div class="form-section">
                <label class="label-title">Deskripsi *</label>
                <textarea class="form-box" readonly>{{ $project->description ?? '' }}</textarea>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-md-6">

            <div class="form-section">
                <label class="label-title d-block">Prioritas *</label>
                <input type="text" class="form-box" value="{{ strtoupper($project->priority ?? '') }}" readonly>
            </div>

        </div>

    </div>


    <!-- Daftar Barang -->
    <div class="section-title mt-4">Daftar Barang</div>

    <button class="btn-item mb-3" id="addItemBtn">Tambah Item</button>

    <div id="itemContainer">

        @foreach($items as $item)
        <div class="row mb-4 item-row">

            <div class="col-md-6">
                <label class="label-title">Nama Barang *</label>
                <input type="text" class="form-box" value="{{ $item->nama_barang }}" readonly>

                <label class="label-title mt-3">Spesifikasi *</label>
                <textarea class="form-box" readonly>{{ $item->spesifikasi }}</textarea>
            </div>

            <div class="col-md-6">
                <label class="label-title">Satuan *</label>
                <input type="text" class="form-box" value="{{ $item->satuan }}" readonly>

                <label class="label-title mt-3">Harga *</label>
                <input type="text" class="form-box" value="Rp. {{ number_format($item->harga,0,',','.') }}" readonly>

                <label class="label-title mt-3">Harga Estimasi *</label>
                <input type="text" class="form-box" value="Rp. {{ number_format($item->harga_estimasi,0,',','.') }}" readonly>
            </div>

        </div>
        @endforeach

    </div>


    <!-- Submit -->
    <form action="{{ route('desain.kirim-pengadaan', $project->project_id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-kirim">Kirim Pengadaan</button>
    </form>

</div>

@endsection


@push('scripts')
<script>
    // Add new input item dynamically
    document.getElementById('addItemBtn').addEventListener('click', function () {
        const container = document.getElementById('itemContainer');

        const html = `
        <div class="row mb-4 item-row">
            <div class="col-md-6">
                <label class="label-title">Nama Barang *</label>
                <input type="text" class="form-box" name="nama_barang[]" required>

                <label class="label-title mt-3">Spesifikasi *</label>
                <textarea class="form-box" name="spesifikasi[]" required></textarea>
            </div>

            <div class="col-md-6">
                <label class="label-title">Satuan *</label>
                <input type="text" class="form-box" name="satuan[]" required>

                <label class="label-title mt-3">Harga *</label>
                <input type="number" class="form-box" name="harga[]" required>

                <label class="label-title mt-3">Harga Estimasi *</label>
                <input type="number" class="form-box" name="harga_estimasi[]" required>
            </div>
        </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
    });
</script>
@endpush
