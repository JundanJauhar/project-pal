@extends('layouts.app')

@section('title', 'Form Pengadaan - PT PAL Indonesia')

@push('styles')
<style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .page-wrapper {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        overflow-x: hidden;
    }

    .form-container {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
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
            padding: 20px 15px;
            align-items: flex-start;
        }

        .form-container {
            max-width: 100%;
        }

        .btn-kirim {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="page-wrapper">
    <div class="form-container">

        <button onclick="history.back()" class="btn btn-secondary mb-4">
            <i class="bi bi-arrow-left-circle me-2"></i>Kembali
        </button>

        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Project:</strong> {{ $project->project_code }} - {{ $project->project_name }}
        </div>

        <form method="POST"
            action="{{ route('desain.input-item.store', $project->project_id) }}"
            id="mainForm">
            @csrf

            <div class="card card-custom">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Tambah Item Baru</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Equipment <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('item_name') is-invalid @enderror"
                            id="item_name" name="item_name"
                            value="{{ old('item_name') }}"
                            placeholder="Nama Equipment" required>
                        @error('item_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">

                        <div class="col-md-12 mb-3">
                            <label for="procurement_id" class="form-label">Procurement <span class="text-danger">*</span></label>
                            <select name="procurement_id" id="procurement_id" class="form-select" required>
                                <option value="">Pilih Procurement</option>
                                @foreach($project->procurements as $procurement)
                                <option value="{{ $procurement->procurement_id }}" {{ old('procurement_id') == $procurement->procurement_id ? 'selected' : '' }}>
                                    {{ $procurement->code_procurement }} - {{ $procurement->name_procurement }}
                                </option>
                                @endforeach
                            </select>
                            @error('procurement_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-select" required>
                                <option value="">Pilih Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id_vendor }}" {{ old('vendor_id') == $vendor->id_vendor ? 'selected' : '' }}>
                                    {{ $vendor->name_vendor }}
                                </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="deadline_date" class="form-label">Tanggal Tenggat <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('deadline_date') is-invalid @enderror"
                                id="deadline_date" name="deadline_date"
                                value="{{ old('deadline_date') }}"
                                required>
                            @error('deadline_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3 d-flex gap-2 justify-content-end">
                            <a href="javascript:history.back()" class="btn btn-secondary btn-custom">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-custom">
                                <i class="bi bi-save"></i> Simpan Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </form>

</div>
</div>

@endsection

@push('scripts')
<script>
    // No additional scripts needed
</script>
@endpush