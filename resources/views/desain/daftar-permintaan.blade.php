    @extends('layouts.app')

    @section('title', 'Daftar Permintaan - PT PAL Indonesia')

    @push('styles')
    <style>

        /* Card abu-abu besar */
        .big-card {
            background: #e8e8e8;
            border-radius: 18px;
            padding: 40px 50px;
            min-height: 550px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.12);
        }

        /* Search bar */
        .search-wrapper {
            width: 40%;
            margin: 0 auto 20px auto;
            position: relative;
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
        }

    </style>
    @endpush


    @section('content')

    <h2 class="fw-bold mb-4">Daftar Permintaan</h2>

    <div class="card big-card">

        {{-- SEARCH BAR --}}
        <div class="search-wrapper">
            <input type="text" class="search-input" placeholder="Cari permintaan...">
            <span class="search-icon">🔍</span>
        </div>

        {{-- TABLE HEADER --}}
        <table class="request-table">
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Vendor</th>
                    <th>Status</th>
                    <th>Information</th>
                    <th>Tanggal Pengadaan</th>
                    <th>
                        <select class="filter-select">
                            <option>All</option>
                            <option>Tepat Waktu</option>
                            <option>Terlambat</option>
                        </select>
                        <div>Tanggal Tenggat</div>
                    </th>
                </tr>
            </thead>

            <tbody>

                {{-- Loop permintaan (PERBAIKAN #3) --}}
                @forelse($project->requestProcurements as $req)
                <tr>
                    <td>
                        <a href="{{ route('desain.review-evatek', $req->request_id) }}" 
                        style="text-decoration: none; color: #000; font-weight: 600;">
                            {{ $req->request_name }}
                        </a>
                    </td>

                    <td>{{ $req->vendor->name_vendor ?? '-' }}</td>
                    <td>{{ $req->request_status }}</td>
                    <td>-</td>
                    <td>{{ \Carbon\Carbon::parse($req->created_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->deadline_date)->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">Belum ada permintaan.</td>
                </tr>
                @endforelse

            </tbody>
        </table>

    </div>

    @endsection
