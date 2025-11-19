@extends('layouts.app')

@section('title', 'List Pengadaan')

@section('content')

<style>
    .approval-card {
        background: #f3f3f3;
        padding: 20px;
        border-radius: 15px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }

    .approval-item {
        background: white;
        border-radius: 12px;
        padding: 18px 24px;
        border: 1px solid #dcdcdc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: 0.2s;
        margin-bottom: 12px;
    }

    .approval-item:hover {
        border-color: #bfbfbf;
    }

    .progress-bar-container {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .progress-track {
        width: 140px;
        height: 4px;
        background: #dcdcdc;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
    }

    .dropdown-icon {
        font-size: 20px;
        transition: 0.2s;
    }

    .rotate {
        transform: rotate(180deg);
    }

    .detail-box {
        background: #ededed;
        padding: 20px;
        border-radius: 12px;
        margin-top: -5px;
        border: 1px solid #dcdcdc;
        display: none;
    }

    .table-detail {
        width: 100%;
        margin-top: 10px;
    }

    .table-detail th {
        font-weight: 600;
        color: #444;
        padding-bottom: 6px;
        border-bottom: 1px solid #bbb;
    }

    .table-detail td {
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
        vertical-align: top;
    }

    .checkbox-inspection {
        width: 22px;
        height: 22px;
        background: #dcdcdc;
        border-radius: 4px;
        display: inline-block;
        margin-right: 4px;
    }

</style>

<h3 class="fw-bold mb-4">List Pengadaan</h3>

<div class="approval-card">

    @foreach($procurements as $proc)

    @php
        $totalItems = $proc->items->count();
        $doneItems = $proc->items->where('inspection_result', '!=', null)->count();
        $progress = $totalItems > 0 ? ($doneItems / $totalItems) * 100 : 0;

        // Progress bar color
        if ($progress < 25) {
            $progressColor = "#c0392b"; // red
        } elseif ($progress < 50) {
            $progressColor = "#f39c12"; // yellow/orange
        } elseif ($progress < 75) {
            $progressColor = "#d35400"; // darker orange
        } else {
            $progressColor = "#27ae60"; // green
        }
    @endphp

    <!-- LIST ITEM -->
    <div class="approval-item" data-target="detail-{{ $proc->id }}">
        <div class="fw-bold">{{ $proc->code }}</div>

        <div class="progress-bar-container">
            <div class="progress-track">
                <div class="progress-fill" style="width: {{ $progress }}%; background: {{ $progressColor }};"></div>
            </div>
        </div>

        <div>{{ \Carbon\Carbon::parse($proc->date)->format('d/m/Y') }}</div>

        <div class="fw-semibold">{{ $proc->priority }}</div>

        <div class="fw-semibold text-muted">{{ $proc->status }}</div>

        <div>
            <i class="bi bi-chevron-down dropdown-icon"></i>
        </div>
    </div>

    <!-- DETAIL BOX (DROPDOWN) -->
    <div id="detail-{{ $proc->id }}" class="detail-box">
        <div class="fw-bold mb-2">{{ $proc->code }}</div>

        <table class="table-detail">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Spesifikasi</th>
                    <th>Jumlah</th>
                    <th>Tanggal Kedatangan</th>
                    <th>Hasil Inspeksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($proc->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->spec }}</td>
                    <td>{{ $item->qty }} {{ $item->unit }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->arrival_date)->format('d/m/Y') }}</td>
                    <td>
                        <span class="checkbox-inspection"></span>
                        <span class="checkbox-inspection"></span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endforeach

</div>


<script>
    document.querySelectorAll('.approval-item').forEach(item => {
        item.addEventListener('click', function() {

            const id = this.getAttribute('data-target');
            const box = document.getElementById(id);

            const icon = this.querySelector('.dropdown-icon');

            if (box.style.display === "block") {
                box.style.display = "none";
                icon.classList.remove("rotate");
            } else {
                box.style.display = "block";
                icon.classList.add("rotate");
            }
        });
    });
</script>

@endsection
