    @extends('layouts.app')

    @section('title', 'Daftar Item - PT PAL Indonesia')

    @push('styles')
    <style>

        /* Card abu-abu besar */
        .big-card {
            background: #ebebeb;
            border-radius: 18px;
            padding: 40px 50px;
            min-height: 550px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.12);
        }

        /* Search bar */
        .search-wrapper {
            width: 40%;
            /* margin: 0 auto 20px auto; */
            position: relative;
            justify-content: space-between;
            display: flex;
            margin-bottom: 25px;
        }

        .search-input {
            width: 100%;
            height: 38px;
            border-radius: 20px;
            border: none;
            padding: 0 45px 0 20px;
            background: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.6;
            cursor: pointer;
        }

        /* Table style */
        .request-table {
            width: 100%;
            margin-top: 25px;
            font-size: 15px;
        }

        .request-table th {
            font-weight: 600;
            color: #222;
            padding-bottom: 15px;
            border-bottom: 1px solid #858585;
        }

        .request-table td {
            padding: 12px 0;
            border-bottom: 1px solid #cfcfcf;
        }

        /* Dropdown */
        .filter-select {
            border-radius: 6px;
            padding: 4px 10px;
            border: 1px solid #bbb;
            background: white;
            font-size: 14px;
            width: 120px;
        }
        .tambah .btn{
        background: #003d82;
        border-color: #003d82;
    }

    </style>
    @endpush


    @section('content')

    <h2 class="fw-bold mb-4">Daftar Item</h2>


    <div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Cari Equipment..." value="">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="not_approved">Not Approved</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="priority">
                            <option value="">Deadline</option>
                            <option value="hari_ini">Hari Ini</option>
                            <option value="satu_minggu">1 Minggu</option>
                            <option value="satu_bulan">1 Bulan</option>
                        </select>
                    </div>
                    <div class="tambah col-md-2 text-end">

                        <a href="{{ route('procurements.create') }}" class="btn btn-primary w-100 btn-custom" wire:navigate>
                            <i class="bi bi-plus-circle"></i> Tambah
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <div class="card big-card">
        {{-- TABLE HEADER --}}
        <table class="request-table">
            <thead>
                <tr>
                    <th style = "padding: 12px 8px; text-align: left;">Equipment</th>
                    <th style = "padding: 12px 8px; text-align: left;">Vendor</th>
                    <th style = "padding: 12px 8px; text-align: center;">Status</th>
                    <th style = "padding: 12px 8px; text-align: center;">Information</th>
                    <th style = "padding: 12px 8px; text-align: center;">Tanggal Pengadaan</th>
                    <th style = "padding: 12px 8px; text-align: center;">Tanggal Tenggat</th>
                </tr>
            </thead>

            <tbody>

                {{-- Loop semua procurement dari project --}}
                @forelse($project->procurements as $procurement)
                    {{-- Loop request procurement dari setiap procurement --}}
                    @foreach($procurement->requestProcurements as $req)
                        {{-- Loop items dari setiap request --}}
                        @forelse($req->items as $item)
                        <tr>
                            <td style = "padding: 12px 8px; text-align: left;">
                                <a href="{{ route('desain.review-evatek', $req->request_id) }}"
                                style="text-decoration: none; color: #000; font-weight: 600;">
                                    {{ $item->item_name }}
                                </a>
                                <div style="font-size: 12px; color: #666;">
                                    {{ $item->amount }} {{ $item->unit }}
                                </div>
                            </td>

                            <td style = "padding: 12px 8px; text-align: left;">{{ $req->vendor->name_vendor ?? '-' }}</td>
                            <td style = "padding: 12px 8px; text-align: center;">
                                @php
                                    $statusMap = [
                                        'draft' => ['Draft', '#6c757d'],
                                        'submitted' => ['Submitted', '#0dcaf0'],
                                        'approved' => ['Approved', '#198754'],
                                        'rejected' => ['Rejected', '#dc3545'],
                                        'completed' => ['Completed', '#28a745'],
                                    ];
                                    [$statusText, $color] = $statusMap[$req->request_status] ?? [ucfirst($req->request_status), '#6c757d'];
                                @endphp
                                <span style="background: {{ $color }}; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td style = "padding: 12px 8px; text-align: center;">
                                <div style="font-size: 13px;">{{ $procurement->code_procurement }}</div>
                                <div style="font-size: 11px; color: #666;">{{ $req->request_name }}</div>
                            </td>
                            <td style = "padding: 12px 8px; text-align: center;">{{ \Carbon\Carbon::parse($req->created_date)->format('d/m/Y') }}</td>
                            <td style = "padding: 12px 8px; text-align: center;">
                                @php
                                    $deadline = \Carbon\Carbon::parse($req->deadline_date);
                                    $now = \Carbon\Carbon::now();
                                    $isLate = $deadline->isPast() && $req->request_status !== 'completed';
                                @endphp
                                <span style="color: {{ $isLate ? '#dc3545' : '#000' }}; font-weight: {{ $isLate ? '600' : '400' }};">
                                    {{ $deadline->format('d/m/Y') }}
                                    @if($isLate)
                                        <small style="display: block; font-size: 10px;">⚠️ Terlambat</small>
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    @endforeach
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">Belum ada permintaan atau item untuk project ini.</td>
                </tr>
                @endforelse

            </tbody>
        </table>

    </div>

    @endsection
