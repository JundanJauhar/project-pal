@extends('layouts.app')

@section('title', 'Daftar Item - PT PAL Indonesia')

@push('styles')
<style>
    .big-card {
        border-radius: 18px;
        padding: 40px 50px;
        min-height: 550px;
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.12);
        border: none;
    }

    .search-wrapper {
        width: 40%;
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
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.6;
        cursor: pointer;
    }

    .request-table {
        width: 100%;
        margin-top: 15px;
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

    .filter-select {
        border-radius: 6px;
        padding: 4px 10px;
        border: 1px solid #bbb;
        background: white;
        font-size: 14px;
        width: 120px;
    }

    .tambah .btn {
        background: #003d82;
        border-color: #003d82;
    }

    .tambah .btn:hover {
        background: #002e5c;
        border-color: #002e5c;
    }

    .status-badge {
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .status-approved {
        color: #28AC00 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
    }

    .status-not-approved {
        color: #BD0000 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
    }

    /* PDF Generation Fix - Ensure content is visible */
    #reportContent {
        position: relative;
        background: white;
    }

    /* When generating PDF, remove scroll constraint */
    .pdf-generating #reportContent {
        max-height: none !important;
        overflow: visible !important;
    }

    /* Ensure gradients render in PDF */
    .pdf-header,
    .analytics-card {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        color-adjust: exact;
    }

    /* Chart containers */
    .chart-card canvas {
        max-width: 100% !important;
        height: auto !important;
    }

    /* Page break hints for PDF */
    .section-header {
        page-break-before: auto;
        page-break-after: avoid;
        page-break-inside: avoid;
    }

    .analytics-card,
    .chart-card {
        page-break-inside: avoid;
    }

    .mb-4 {
        page-break-inside: avoid;
    }

    /* Print styles */
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            background: #fff !important;
        }

        /* Bootstrap modal centering breaks print layout: force top-aligned flow */
        #summaryReportModal,
        .modal,
        .modal-dialog,
        .modal-content {
            position: static !important;
            transform: none !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: none !important;
            box-shadow: none !important;
        }

        .modal-backdrop {
            display: none !important;
        }

        .modal-dialog-centered {
            min-height: 0 !important;
            align-items: flex-start !important;
        }

        .modal-header,
        .modal-footer,
        .btn {
            display: none !important;
        }

        /* Robust print isolation (works even if modal is nested inside wrappers) */
        body * {
            visibility: hidden;
        }

        #summaryReportModal,
        #summaryReportModal *,
        #summaryReportTableModal,
        #summaryReportTableModal * {
            visibility: visible;
        }

        #reportContent,
        #summaryTableContent {
            /* Pin printable content to top of the page */
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-height: none !important;
            overflow: visible !important;
            padding: 0 !important;
        }

        /* PRINT output uses simplified table template */
        #reportContent> :not(#reportPrint) {
            display: none !important;
        }

        #reportPrint {
            display: block !important;
            font-size: 10px !important;
            line-height: 1.25 !important;
        }

        /* Summary Report table print */
        #summaryTablePrint {
            display: block !important;
            font-size: 10px !important;
            line-height: 1.25 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Daftar Item</h2>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" id="btnDashboard" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 10px 24px; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            <i class="fas fa-chart-pie me-2"></i>Dashboard
        </button>
        <button class="btn btn-primary" id="btnSummaryReport" style="background: linear-gradient(135deg, #1a5276 0%, #1f6f8b 100%); border: none; padding: 10px 24px; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 15px rgba(26, 82, 118, 0.4);">
            <i class="fas fa-table me-2"></i>Summary Report
        </button>
    </div>
</div>

<!-- Summary Report Modal with Analytics Dashboard -->
<div class="modal fade" id="summaryReportModal" tabindex="-1" aria-labelledby="summaryReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 18px 18px 0 0; padding: 20px 30px;">
                <h5 class="modal-title text-white fw-bold" id="summaryReportModalLabel">
                    <i class="fas fa-chart-pie me-2"></i>Analytics Dashboard
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm" id="btnDownloadPDF" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm" id="btnPrintPDF" style="border-radius: 8px; font-weight: 600;" title="Alternative: Print to PDF">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" style="padding: 30px; max-height: 80vh; overflow-y: auto;" id="reportContent">
                <!-- Simplified (Print/PDF) Report: tables only, no colors -->
                <div id="reportPrint" style="display: none; font-family: Arial, sans-serif; color: #111; font-size: 10px; line-height: 1.25;">
                    <div style="text-align:center; margin-bottom: 8px;">
                        <div style="font-size: 11px; letter-spacing: 1px;">PT PAL INDONESIA (PERSERO)</div>
                        <div style="font-size: 16px; font-weight: 800; margin-top: 2px;">LAPORAN SUMMARY EVATEK</div>
                        <div style="font-size: 10px; margin-top: 2px;">Divisi Desain - Project Pal System</div>
                        <div style="font-size: 9px; margin-top: 4px;">Generated: <span id="printReportDate">-</span></div>
                    </div>

                    <div style="border-top: 1px solid #333; margin: 6px 0 8px;"></div>

                    <div style="font-weight: 700; margin-bottom: 4px;">A. Ringkasan Status</div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #333; padding: 4px; text-align:left;">Metrik</th>
                                <th style="border: 1px solid #333; padding: 4px; text-align:right; width: 64px;">Jumlah</th>
                                <th style="border: 1px solid #333; padding: 4px; text-align:right; width: 64px;">%</th>
                                <th style="border: 1px solid #333; padding: 4px; text-align:left;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid #333; padding: 4px;">Total Items</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;" id="printTotalItems">0</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;">-</td>
                                <td style="border: 1px solid #333; padding: 4px;">Jumlah keseluruhan item Evatek</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #333; padding: 4px;">Approved</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;" id="printApproved">0</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;" id="printApprovedPct">0%</td>
                                <td style="border: 1px solid #333; padding: 4px;">Item sudah disetujui (selesai)</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #333; padding: 4px;">On Progress</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;" id="printOnProgress">0</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;" id="printOnProgressPct">0%</td>
                                <td style="border: 1px solid #333; padding: 4px;">Item dalam proses review / berjalan</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #333; padding: 4px;">Not Approved</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;" id="printNotApproved">0</td>
                                <td style="border: 1px solid #333; padding: 4px; text-align:right;" id="printNotApprovedPct">0%</td>
                                <td style="border: 1px solid #333; padding: 4px;">Item ditolak / perlu revisi</td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="margin-top: 4px; font-size: 9px; color: #222;">
                        Ringkasan cepat: Tingkat persetujuan (Approved) = <strong id="printApprovalRate">0%</strong>.
                    </div>

                    <div style="margin-top: 8px; width: 100%; display: table; table-layout: fixed;">
                        <div style="display: table-cell; width: 50%; padding-right: 6px; vertical-align: top;">
                            <div style="font-weight: 700; margin-bottom: 4px;">B. Distribusi PIC</div>
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #333; padding: 4px; text-align:left;">PIC</th>
                                        <th style="border: 1px solid #333; padding: 4px; text-align:right; width: 56px;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody id="printPicRows">
                                    <tr>
                                        <td style="border: 1px solid #333; padding: 4px;" colspan="2">Tidak ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div style="display: table-cell; width: 50%; padding-left: 6px; vertical-align: top;">
                            <div style="font-weight: 700; margin-bottom: 4px;">C. Distribusi Vendor</div>
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #333; padding: 4px; text-align:left;">Vendor</th>
                                        <th style="border: 1px solid #333; padding: 4px; text-align:right; width: 56px;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody id="printVendorRows">
                                    <tr>
                                        <td style="border: 1px solid #333; padding: 4px;" colspan="2">Tidak ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>



                    <div style="margin-top: 8px;">
                        <div style="font-weight: 700; margin-bottom: 4px;">E. Keterangan</div>
                        <div style="border: 1px solid #333; padding: 6px; font-size: 9px; color: #222;">
                            <div>1) <strong>Approved</strong> = item disetujui dan proses selesai.</div>
                            <div>2) <strong>On Progress</strong> = item masih dikerjakan / menunggu review.</div>
                            <div>3) <strong>Not Approved</strong> = item ditolak atau perlu revisi/tindak lanjut.</div>
                            <div>4) Distribusi <strong>PIC</strong> dan <strong>Vendor</strong> menunjukkan jumlah item terkait.</div>
                        </div>
                    </div>

                    <div style="margin-top: 8px;">
                        <div style="font-weight: 700; margin-bottom: 4px;">F. Catatan (Ringkas)</div>
                        <div id="printNotes" style="border: 1px solid #333; padding: 6px; min-height: 26px; font-size: 10px; white-space: pre-wrap;">Tidak ada catatan.</div>
                        <div style="margin-top: 3px; font-size: 8.5px; color: #333;">Catatan panjang otomatis diringkas untuk menjaga 1 halaman.</div>
                    </div>

                    <div style="margin-top: 6px; font-size: 9px; color: #333; text-align:center;">
                        Dokumen ini digenerate otomatis oleh sistem Project Pal
                    </div>
                </div>

                <!-- PDF Header -->
                <div class="pdf-header mb-4" style="background: linear-gradient(135deg, #003d82 0%, #001f4d 100%); border-radius: 14px; padding: 25px 30px; color: white; text-align: center;">
                    <div style="font-size: 12px; opacity: 0.8; letter-spacing: 2px;">PT PAL INDONESIA (PERSERO)</div>
                    <div style="font-size: 24px; font-weight: 800; margin-top: 5px;">LAPORAN SUMMARY EVATEK</div>
                    <div style="font-size: 13px; opacity: 0.9; margin-top: 8px;">Divisi Desain - Project Pal System</div>
                </div>

                <!-- Section 1: Ringkasan Status -->
                <div class="section-header mb-2" style="border-left: 4px solid #003d82; padding-left: 12px;">
                    <h6 class="fw-bold mb-1" style="color: #003d82; font-size: 14px;">1. RINGKASAN STATUS ITEM</h6>
                    <p style="font-size: 11px; color: #666; margin-bottom: 0;">Menampilkan jumlah total item dan breakdown berdasarkan status proses Evatek.</p>
                </div>

                <!-- Summary Cards Row -->
                <div class="row mb-2">
                    <div class="col-md-3">
                        <div class="analytics-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 14px; padding: 20px; color: white;">
                            <div style="font-size: 12px; opacity: 0.8;">TOTAL ITEMS</div>
                            <div style="font-size: 36px; font-weight: 800;" id="summaryTotalItems">0</div>
                            <div style="font-size: 11px; opacity: 0.7;"><i class="fas fa-box me-1"></i>Semua Item</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="analytics-card" style="background: linear-gradient(135deg, #28AC00 0%, #1e8a00 100%); border-radius: 14px; padding: 20px; color: white;">
                            <div style="font-size: 12px; opacity: 0.8;">APPROVED</div>
                            <div style="font-size: 36px; font-weight: 800;" id="summaryApproved">0</div>
                            <div style="font-size: 11px; opacity: 0.7;"><i class="fas fa-check-circle me-1"></i>Disetujui</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="analytics-card" style="background: linear-gradient(135deg, #FF9500 0%, #e68a00 100%); border-radius: 14px; padding: 20px; color: white;">
                            <div style="font-size: 12px; opacity: 0.8;">ON PROGRESS</div>
                            <div style="font-size: 36px; font-weight: 800;" id="summaryOnProgress">0</div>
                            <div style="font-size: 11px; opacity: 0.7;"><i class="fas fa-spinner me-1"></i>Dalam Proses</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="analytics-card" style="background: linear-gradient(135deg, #BD0000 0%, #990000 100%); border-radius: 14px; padding: 20px; color: white;">
                            <div style="font-size: 12px; opacity: 0.8;">NOT APPROVED</div>
                            <div style="font-size: 36px; font-weight: 800;" id="summaryNotApproved">0</div>
                            <div style="font-size: 11px; opacity: 0.7;"><i class="fas fa-times-circle me-1"></i>Ditolak</div>
                        </div>
                    </div>
                </div>
                <div class="mb-4" style="background: #f0f4f8; border-radius: 8px; padding: 10px 15px; font-size: 11px; color: #555;">
                    <i class="fas fa-info-circle me-1" style="color: #003d82;"></i>
                    <strong>Keterangan:</strong> Total Items adalah jumlah keseluruhan item Evatek. Approved = item yang sudah disetujui, On Progress = item dalam proses review, Not Approved = item yang ditolak dan perlu revisi.
                </div>

                <!-- Interpretasi Otomatis -->
                <div id="autoInterpretation" class="mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border-radius: 10px; padding: 15px 20px; border-left: 4px solid #1976D2;">
                    <h6 class="fw-bold mb-2" style="color: #1565C0; font-size: 13px;"><i class="fas fa-lightbulb me-2"></i>Interpretasi Data</h6>
                    <div id="interpretationText" style="font-size: 11px; color: #444; line-height: 1.7;"></div>
                </div>

                <!-- Section 2: Visualisasi Chart -->
                <div class="section-header mb-2" style="border-left: 4px solid #003d82; padding-left: 12px;">
                    <h6 class="fw-bold mb-1" style="color: #003d82; font-size: 14px;">2. VISUALISASI DATA</h6>
                    <p style="font-size: 11px; color: #666; margin-bottom: 0;">Grafik distribusi status dan pembagian item berdasarkan PIC (Person In Charge).</p>
                </div>

                <!-- Charts Row -->
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="chart-card" style="background: #fff; border-radius: 14px; padding: 20px; border: 1px solid #eee;">
                            <h6 class="fw-bold mb-3" style="color: #444;"><i class="fas fa-chart-pie me-2"></i>Status Distribution</h6>
                            <div style="position: relative; height: 200px; width: 100%;">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-card" style="background: #fff; border-radius: 14px; padding: 20px; border: 1px solid #eee;">
                            <h6 class="fw-bold mb-3" style="color: #444;"><i class="fas fa-chart-bar me-2"></i>Items by PIC</h6>
                            <div style="position: relative; height: 200px; width: 100%;">
                                <canvas id="picChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4" style="background: #f0f4f8; border-radius: 8px; padding: 10px 15px; font-size: 11px; color: #555;">
                    <i class="fas fa-info-circle me-1" style="color: #003d82;"></i>
                    <strong>Keterangan:</strong> Diagram kiri (Doughnut Chart) menunjukkan proporsi status item dalam persentase dari total - semakin besar area warna hijau, semakin tinggi tingkat penyelesaian. Diagram kanan (Bar Chart) menampilkan distribusi beban kerja per PIC divisi - tinggi bar menunjukkan jumlah item yang menjadi tanggung jawab masing-masing PIC.
                </div>

                <!-- Data Numerik Chart -->
                <div class="mb-4" style="background: #fff9e6; border-radius: 10px; padding: 15px 20px; border-left: 4px solid #FF9500;">
                    <h6 class="fw-bold mb-2" style="color: #F57C00; font-size: 12px;"><i class="fas fa-calculator me-2"></i>Ringkasan Numerik Visualisasi</h6>
                    <div id="chartNumericSummary" style="font-size: 11px; color: #444; line-height: 1.6;"></div>
                </div>

                <!-- Section 3: Progress Keseluruhan -->
                <div class="section-header mb-2" style="border-left: 4px solid #003d82; padding-left: 12px;">
                    <h6 class="fw-bold mb-1" style="color: #003d82; font-size: 14px;">3. PROGRESS KESELURUHAN</h6>
                    <p style="font-size: 11px; color: #666; margin-bottom: 0;">Indikator persentase penyelesaian proses Evatek.</p>
                </div>

                <!-- Progress Bar -->
                <div class="mb-2" style="background: #f8f9fa; border-radius: 14px; padding: 20px;">
                    <div class="progress" style="height: 30px; border-radius: 15px; background: #e9ecef;">
                        <div class="progress-bar" id="progressApproved" role="progressbar" style="background: #28AC00;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        <div class="progress-bar" id="progressOnProgress" role="progressbar" style="background: #FF9500;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="progress-bar" id="progressNotApproved" role="progressbar" style="background: #BD0000;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2" style="font-size: 12px; color: #666;">
                        <span><span style="display:inline-block; width:12px; height:12px; background:#28AC00; border-radius:3px; margin-right:5px;"></span>Approved: <span id="progressApprovedPct">0%</span></span>
                        <span><span style="display:inline-block; width:12px; height:12px; background:#FF9500; border-radius:3px; margin-right:5px;"></span>On Progress: <span id="progressOnProgressPct">0%</span></span>
                        <span><span style="display:inline-block; width:12px; height:12px; background:#BD0000; border-radius:3px; margin-right:5px;"></span>Not Approved: <span id="progressNotApprovedPct">0%</span></span>
                    </div>
                </div>
                <div class="mb-4" style="background: #f0f4f8; border-radius: 8px; padding: 10px 15px; font-size: 11px; color: #555;">
                    <i class="fas fa-info-circle me-1" style="color: #003d82;"></i>
                    <strong>Keterangan:</strong> Progress bar menunjukkan persentase dari total item. Warna hijau (Approved) menunjukkan item selesai, kuning (On Progress) sedang diproses, merah (Not Approved) perlu tindak lanjut.
                </div>

                <!-- Status Progress Detail -->
                <div class="mb-4" style="background: #e8f5e9; border-radius: 10px; padding: 15px 20px; border-left: 4px solid #28AC00;">
                    <h6 class="fw-bold mb-2" style="color: #2E7D32; font-size: 12px;"><i class="fas fa-tasks me-2"></i>Analisis Tingkat Penyelesaian</h6>
                    <div id="progressAnalysis" style="font-size: 11px; color: #444; line-height: 1.6;"></div>
                </div>

                <!-- Section 4: Breakdown Detail -->
                <div class="section-header mb-2" style="border-left: 4px solid #003d82; padding-left: 12px;">
                    <h6 class="fw-bold mb-1" style="color: #003d82; font-size: 14px;">4. BREAKDOWN DETAIL</h6>
                    <p style="font-size: 11px; color: #666; margin-bottom: 0;">Rincian item berdasarkan PIC dan Vendor.</p>
                </div>

                <!-- Vendor & PIC Detail Row -->
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div style="background: #fff; border-radius: 14px; padding: 20px; border: 1px solid #eee;">
                            <h6 class="fw-bold mb-3" style="color: #444;"><i class="fas fa-user-tie me-2"></i>By PIC</h6>
                            <div id="summaryByPic" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: #fff; border-radius: 14px; padding: 20px; border: 1px solid #eee;">
                            <h6 class="fw-bold mb-3" style="color: #444;"><i class="fas fa-building me-2"></i>By Vendor</h6>
                            <div id="summaryByVendor" style="max-height: 150px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="mb-4" style="background: #f0f4f8; border-radius: 8px; padding: 10px 15px; font-size: 11px; color: #555;">
                    <i class="fas fa-info-circle me-1" style="color: #003d82;"></i>
                    <strong>Keterangan:</strong> Breakdown PIC menunjukkan distribusi item per divisi penanggung jawab. Breakdown Vendor menampilkan jumlah item yang dikerjakan oleh masing-masing vendor/supplier.
                </div>

                <!-- Vendor & PIC Summary -->
                <div class="mb-4" style="background: #fce4ec; border-radius: 10px; padding: 15px 20px; border-left: 4px solid #C2185B;">
                    <h6 class="fw-bold mb-2" style="color: #AD1457; font-size: 12px;"><i class="fas fa-users-cog me-2"></i>Ringkasan Distribusi Kerja</h6>
                    <div id="distributionSummary" style="font-size: 11px; color: #444; line-height: 1.6;"></div>
                </div>

                <!-- Section 5: Catatan -->
                <div class="section-header mb-2" style="border-left: 4px solid #003d82; padding-left: 12px;">
                    <h6 class="fw-bold mb-1" style="color: #003d82; font-size: 14px;">5. CATATAN & KETERANGAN TAMBAHAN</h6>
                    <p style="font-size: 11px; color: #666; margin-bottom: 0;">Catatan khusus dari pembuat laporan.</p>
                </div>

                <!-- Notes/Keterangan Section -->
                <div class="notes-section" style="background: #fff; border-radius: 14px; padding: 20px; border: 1px solid #eee;">
                    <textarea id="reportNotes" class="form-control" rows="3" placeholder="Tambahkan catatan atau keterangan untuk laporan ini..." style="border: 1px solid #ddd; border-radius: 10px; resize: none;"></textarea>
                    <div id="reportNotesDisplay" style="display: none; white-space: pre-wrap; font-size: 13px; color: #444; line-height: 1.6; min-height: 50px;"></div>
                </div>

                <!-- Report Footer -->
                <div class="mt-4" style="background: #f8f9fa; border-radius: 10px; padding: 15px 20px;">
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 11px; color: #666;">
                        <div>
                            <i class="fas fa-file-pdf me-1"></i>Dokumen ini digenerate otomatis oleh sistem Project Pal
                        </div>
                        <div>
                            <i class="fas fa-clock me-1"></i>Report Generated: <span id="reportDate"></span>
                        </div>
                    </div>
                    <div class="text-center mt-2" style="font-size: 10px; color: #999;">
                        © 2026 PT PAL Indonesia (Persero) - Divisi Desain | Dokumen Rahasia Perusahaan
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px 30px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Report Modal (Detail Table Only) -->
<div class="modal fade" id="summaryReportTableModal" tabindex="-1" aria-labelledby="summaryReportTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a5276 0%, #1f6f8b 100%); border-radius: 18px 18px 0 0; padding: 20px 30px;">
                <h5 class="modal-title text-white fw-bold" id="summaryReportTableModalLabel">
                    <i class="fas fa-table me-2"></i>Summary Report - Detail Item
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm" id="btnDownloadSummaryPDF" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm" id="btnPrintSummaryTable" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" style="padding: 30px; max-height: 80vh; overflow-y: auto;" id="summaryTableContent">
                <!-- Print template for Summary Report -->
                <div id="summaryTablePrint" style="display: none; font-family: Arial, sans-serif; color: #111; font-size: 10px; line-height: 1.25;">
                    <div style="text-align:center; margin-bottom: 8px;">
                        <div style="font-size: 11px; letter-spacing: 1px;">PT PAL INDONESIA (PERSERO)</div>
                        <div style="font-size: 16px; font-weight: 800; margin-top: 2px;">SUMMARY REPORT - DETAIL ITEM EVATEK</div>
                        <div style="font-size: 10px; margin-top: 2px;">Divisi Desain - Project Pal System</div>
                        <div style="font-size: 9px; margin-top: 4px;">Generated: <span id="printSummaryTableDate">-</span></div>
                    </div>
                    <div style="border-top: 1px solid #333; margin: 6px 0 8px;"></div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 8px;">
                        <thead>
                            <tr style="background: #1a5276; color: white;">
                                <th style="border: 1px solid #333; padding: 3px; text-align: center; width: 20px;">NO.</th>
                                <th style="border: 1px solid #333; padding: 3px; text-align: left;">PERMASALAHAN</th>
                                <th style="border: 1px solid #333; padding: 3px; text-align: left;">HASIL RAPAT</th>
                                <th style="border: 1px solid #333; padding: 3px; text-align: center; width: 55px;">USED DATE</th>
                                <th style="border: 1px solid #333; padding: 3px; text-align: center; width: 55px;">STATUS PENGADAAN</th>
                                <th style="border: 1px solid #333; padding: 3px; text-align: center; width: 45px;">AKSI</th>
                                <th style="border: 1px solid #333; padding: 3px; text-align: center; width: 55px;">TARGET</th>
                                <th style="border: 1px solid #333; padding: 3px; text-align: center; width: 40px;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="printDetailRows">
                            <tr>
                                <td style="border: 1px solid #333; padding: 3px;" colspan="8">Tidak ada data</td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="margin-top: 6px; font-size: 9px; color: #333; text-align:center;">
                        Dokumen ini digenerate otomatis oleh sistem Project Pal
                    </div>
                </div>

                <!-- Header -->
                <div class="mb-4" style="background: linear-gradient(135deg, #1a5276 0%, #154360 100%); border-radius: 14px; padding: 25px 30px; color: white; text-align: center;">
                    <div style="font-size: 12px; opacity: 0.8; letter-spacing: 2px;">PT PAL INDONESIA (PERSERO)</div>
                    <div style="font-size: 24px; font-weight: 800; margin-top: 5px;">SUMMARY REPORT - DETAIL ITEM</div>
                    <div style="font-size: 13px; opacity: 0.9; margin-top: 8px;">Divisi Desain - Project Pal System</div>
                </div>

                <!-- Detail Table -->
                <div class="mb-2" style="background: #fff; border-radius: 14px; padding: 20px; border: 1px solid #eee; overflow-x: auto;">
                    <table id="summaryDetailTable" style="width: 100%; border-collapse: collapse; font-size: 12px; min-width: 800px;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #1a5276, #1f6f8b); color: white;">
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: center; width: 40px; font-weight: 700;">NO.</th>
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: left; min-width: 150px; font-weight: 700;">PERMASALAHAN</th>
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: left; min-width: 220px; font-weight: 700;">HASIL RAPAT</th>
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: center; width: 90px; font-weight: 700;">USED DATE<br><span style="font-size: 9px; font-weight: 400; opacity: 0.8;">(INSTALASI)</span></th>
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: center; width: 100px; font-weight: 700;">STATUS PENGADAAN</th>
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: center; width: 80px; font-weight: 700;">AKSI</th>
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: center; width: 90px; font-weight: 700;">TARGET</th>
                                <th style="border: 1px solid #154360; padding: 10px 8px; text-align: center; width: 70px; font-weight: 700;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="summaryDetailBody">
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px; color: #999;">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mb-4" style="background: #f0f4f8; border-radius: 8px; padding: 10px 15px; font-size: 11px; color: #555;">
                    <i class="fas fa-info-circle me-1" style="color: #003d82;"></i>
                    <strong>Keterangan:</strong> Tabel ini menampilkan detail setiap item Evatek. Kolom "Hasil Rapat" berisi catatan log dan revisi terkini. Status "Open" berarti item masih dalam proses, "Closed" berarti sudah selesai (Approved/Not Approved).
                </div>

                <!-- Footer -->
                <div style="background: #f8f9fa; border-radius: 10px; padding: 15px 20px;">
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 11px; color: #666;">
                        <div><i class="fas fa-file-pdf me-1"></i>Dokumen ini digenerate otomatis oleh sistem Project Pal</div>
                        <div><i class="fas fa-clock me-1"></i>Report Generated: <span id="summaryTableDate"></span></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px 30px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="search-input" name="search" placeholder="Cari Item..." value="">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="status-filter" name="status">
                            <option value="">Semua Status</option>
                            <option value="on_progress">On Progress</option>
                            <option value="approve">Approved</option>
                            <option value="not_approve">Not Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="deadline-filter" name="deadline">
                            <option value="">Semua Target</option>
                            <option value="hari_ini">Hari Ini</option>
                            <option value="satu_minggu">1 Minggu</option>
                            <option value="satu_bulan">1 Bulan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="pic-filter" name="PIC">
                            <option value="">Semua PIC</option>
                            <option value="EO">EO</option>
                            <option value="HC">HC</option>
                            <option value="MO">MO</option>
                            <option value="HO">HO</option>
                            <option value="SEWACO">SEWACO</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card big-card">
    <table class="request-table">
        <thead>
            <tr>
                <th style="padding: 12px 8px; text-align: left;">Item</th>
                <th style="padding: 12px 8px; text-align: left;">Vendor</th>
                <th style="padding: 12px 8px; text-align: left;">PIC</th>
                <th style="padding: 12px 8px; text-align: center;">Start Date</th>
                <th style="padding: 12px 8px; text-align: center;">Target Date</th>
                <th style="padding: 12px 8px; text-align: center;">Link Evatek</th>
                <th style="padding: 12px 8px; text-align: center;">Revision</th>
                <th style="padding: 12px 8px; text-align: center;">Posisi</th>
                <th style="padding: 12px 8px; text-align: center;">Status</th>
                <th style="padding: 12px 8px; text-align: center;">Last Update</th>
            </tr>
        </thead>

        <tbody id="items-tbody">
            @forelse($evatekItems as $evatek)
            @php
            $item = $evatek->item;
            $proc = $evatek->procurement ?? null;
            $proj = $proc ? $proc->project : null;
            $latestRevision = $evatek->latestRevision;
            $status = $latestRevision ? $latestRevision->status : 'pending';
            $catatan = $latestRevision ? ($latestRevision->catatan_approval ?? $latestRevision->alasan_reject ?? '-') : '-';
            @endphp

            @php
            $revisionsData = $evatek->revisions->map(function($rev) {
            return [
            'code' => $rev->revision_code ?? '-',
            'status' => $rev->status ?? '-',
            'date' => $rev->date ? \Carbon\Carbon::parse($rev->date)->format('d/m/Y') : '-',
            'log' => $rev->log ?? '-',
            'approved_at' => $rev->approved_at ? \Carbon\Carbon::parse($rev->approved_at)->format('d/m/Y H:i') : null,
            'not_approved_at' => $rev->not_approved_at ? \Carbon\Carbon::parse($rev->not_approved_at)->format('d/m/Y H:i') : null,
            ];
            })->toArray();
            @endphp
            <tr data-status="{{ $evatek->status }}" data-pic="{{ $evatek->pic_evatek }}" data-target="{{ $evatek->target_date }}" data-revisions='@json($revisionsData)' data-log="{{ $evatek->log ?? '' }}" class="evatek-row">
                <td style="padding: 12px 8px; text-align: left;">
                    <a href="{{ route('desain.review-evatek', $evatek->evatek_id) }}"
                        data-evatek-id="{{ $evatek->evatek_id }}"
                        class="evatek-link"
                        style="text-decoration: none; color: #000; font-weight: 600;">
                        {{ $evatek->item->item_name ?? 'N/A' }}
                        @if(isset($unreadEvatekIds) && in_array($evatek->evatek_id, $unreadEvatekIds))
                        <span class="badge bg-danger ms-2" style="font-size: 10px;">Baru</span>
                        @endif
                    </a>
                </td>

                <td style="padding: 12px 8px; text-align: left;">
                    {{ $evatek->vendor->name_vendor ?? '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: left; color: #1976D2; ">
                    {{ $evatek->pic_evatek ?? '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->start_date ? \Carbon\Carbon::parse($evatek->start_date)->format('d/m/Y') : '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->target_date ? \Carbon\Carbon::parse($evatek->target_date)->format('d/m/Y') : '-' }}
                </td>
                <td style="padding: 12px 8px; text-align: center;">
                    @if($evatek->sc_design_link)
                    <a href="{{$evatek->sc_design_link}}" target="_blank">Link</a>
                    @else
                    -
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->current_revision }}
                </td>

                {{-- Posisi --}}
                <td style="padding: 12px 8px; text-align: center;">
                    @if(in_array($status, ['approve', 'not approve']))
                    <span class="text-muted">-</span>
                    @elseif(empty(trim($latestRevision->vendor_link ?? '')))
                    <span class="badge bg-warning text-dark">Evatek Vendor</span>
                    @else
                    <span class="badge bg-info text-dark">Evatek Desain</span>
                    @endif
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    <span class="status-badge 
                        @if($evatek->status === 'approve') status-approved
                        @elseif($evatek->status === 'not_approve') status-not-approved
                        @else
                        @endif
                    " style="
                        @if($evatek->status === 'approve')
                            color: #28AC00 !important;
                        @elseif($evatek->status === 'not_approve')
                            color: #BD0000 !important;
                        @else
                            color: #FF9500 !important;
                        @endif
                    ">
                        {{ ucfirst($evatek->status) }}
                    </span>
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->current_date ? \Carbon\Carbon::parse($evatek->current_date)->format('d/m/Y') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5">Belum ada item evatek untuk project ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<!-- PDF libs (explicit) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const statusFilter = document.getElementById('status-filter');
        const deadlineFilter = document.getElementById('deadline-filter');
        const picFilter = document.getElementById('pic-filter');
        const tbody = document.getElementById('items-tbody');
        const allRows = tbody.querySelectorAll('tr.evatek-row');
        const CLICKED_KEY = 'clicked_evatek_items_v3'; // Versioned to auto-reset after DB changes

        // Ambil data dari localStorage
        let clickedItems = JSON.parse(localStorage.getItem(CLICKED_KEY)) || [];

        document.querySelectorAll('.evatek-link').forEach(link => {
            const evatekId = link.dataset.evatekId;
            const badge = link.querySelector('.badge');

            // Sembunyikan badge kalau sudah pernah diklik
            if (clickedItems.includes(evatekId) && badge) {
                badge.style.display = 'none';
            }

            // Saat diklik
            link.addEventListener('click', function() {
                if (badge) badge.style.display = 'none';

                if (!clickedItems.includes(evatekId)) {
                    clickedItems.push(evatekId);
                    localStorage.setItem(CLICKED_KEY, JSON.stringify(clickedItems));
                }
            });
        });

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            const selectedDeadline = deadlineFilter.value;
            const selectedPic = picFilter.value;
            const now = new Date();

            allRows.forEach(row => {
                const itemLink = row.querySelector('a');
                const itemName = itemLink ? itemLink.textContent.toLowerCase() : '';
                const status = row.getAttribute('data-status');
                const pic = row.getAttribute('data-pic');
                const target = row.getAttribute('data-target');

                //Filter by Target
                let matchesTarget = true;

                if (selectedDeadline && target) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    const targetDate = new Date(target);
                    targetDate.setHours(0, 0, 0, 0);

                    const diffTime = targetDate - today;
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);

                    if (selectedDeadline === 'hari_ini') {
                        matchesTarget = diffDays === 0;
                    } else if (selectedDeadline === 'satu_minggu') {
                        matchesTarget = diffDays >= 0 && diffDays <= 7;
                    } else if (selectedDeadline === 'satu_bulan') {
                        matchesTarget = diffDays >= 0 && diffDays <= 30;
                    }
                }

                // Filter by PIC
                const matchesPic = !selectedPic || pic === selectedPic;

                // Filter by search
                const matchesSearch = itemName.includes(searchTerm);

                // Filter by status
                const matchesStatus = !selectedStatus || status === selectedStatus;

                // Show/hide row
                if (matchesSearch && matchesStatus && matchesPic && matchesTarget) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);
        deadlineFilter.addEventListener('change', filterTable);
        picFilter.addEventListener('change', filterTable);

        // ===============================
        // DASHBOARD & SUMMARY REPORT
        // ===============================
        const btnDashboard = document.getElementById('btnDashboard');
        const btnSummaryReport = document.getElementById('btnSummaryReport');
        let statusChartInstance = null;
        let picChartInstance = null;

        // Dashboard button: opens analytics modal
        btnDashboard.addEventListener('click', function() {
            generateSummaryReport();
            const modal = new bootstrap.Modal(document.getElementById('summaryReportModal'));
            modal.show();
        });

        // Summary Report button: opens detail table modal
        btnSummaryReport.addEventListener('click', function() {
            const rows = document.querySelectorAll('#items-tbody tr.evatek-row');
            generateDetailTable(rows);
            const dateStr = new Date().toLocaleString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            const summaryTableDate = document.getElementById('summaryTableDate');
            if (summaryTableDate) summaryTableDate.textContent = dateStr;
            const modal = new bootstrap.Modal(document.getElementById('summaryReportTableModal'));
            modal.show();
        });

        // Print for Summary Report table modal
        document.getElementById('btnPrintSummaryTable').addEventListener('click', function() {
            const dateStr = new Date().toLocaleString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            const printDate = document.getElementById('printSummaryTableDate');
            if (printDate) printDate.textContent = dateStr;

            // Temporarily show print template and hide visual content for printing
            const printDiv = document.getElementById('summaryTablePrint');
            const contentDiv = document.getElementById('summaryTableContent');
            if (printDiv) printDiv.style.display = 'block';

            // Hide all other content in the modal body except print template
            const children = contentDiv.children;
            const hiddenEls = [];
            for (let i = 0; i < children.length; i++) {
                if (children[i].id !== 'summaryTablePrint' && children[i].style.display !== 'none') {
                    children[i].dataset.wasVisible = 'true';
                    children[i].style.display = 'none';
                    hiddenEls.push(children[i]);
                }
            }

            window.print();

            // Restore visibility
            setTimeout(() => {
                if (printDiv) printDiv.style.display = 'none';
                hiddenEls.forEach(el => {
                    el.style.display = '';
                    delete el.dataset.wasVisible;
                });
            }, 500);
        });

        // Download PDF for Summary Report table modal
        document.getElementById('btnDownloadSummaryPDF').addEventListener('click', function() {
            const btn = this;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
            btn.disabled = true;

            const reportRoot = document.getElementById('summaryTableContent');

            setTimeout(async () => {
                let captureHost = null;
                try {
                    const jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
                    if (typeof html2canvas === 'undefined' || !jsPDFConstructor) {
                        throw new Error('Library PDF belum siap (html2canvas/jsPDF). Coba refresh (Ctrl+F5).');
                    }

                    captureHost = document.createElement('div');
                    captureHost.style.cssText = [
                        'position: fixed', 'left: -9999px', 'top: 0', 'width: 1200px',
                        'background: #ffffff', 'pointer-events: none',
                        'z-index: -1', 'padding: 0', 'margin: 0'
                    ].join(';');

                    const clone = reportRoot.cloneNode(true);
                    clone.style.display = 'block';
                    clone.style.width = '1200px';
                    clone.style.maxWidth = '1200px';
                    clone.style.background = '#ffffff';
                    clone.style.padding = '20px';
                    clone.style.margin = '0';
                    clone.style.overflow = 'visible';
                    clone.style.maxHeight = 'none';

                    // Remove print template from captured output
                    const printTemplate = clone.querySelector('#summaryTablePrint');
                    if (printTemplate && printTemplate.parentNode) printTemplate.parentNode.removeChild(printTemplate);

                    captureHost.appendChild(clone);
                    document.body.appendChild(captureHost);
                    document.body.classList.add('pdf-generating');

                    const canvas = await html2canvas(captureHost, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        backgroundColor: '#ffffff',
                        width: captureHost.scrollWidth,
                        height: captureHost.scrollHeight,
                        windowWidth: captureHost.scrollWidth
                    });

                    // Portrait A4 — scale wide capture to fit
                    const pageWidth = 210;
                    const pageHeight = 297;
                    const margin = 8;
                    const contentWidth = pageWidth - margin * 2;
                    const pxPerMm = canvas.width / contentWidth;
                    const maxContentHeightMm = pageHeight - margin * 2;
                    const maxContentHeightPx = maxContentHeightMm * pxPerMm;

                    const pdf = new jsPDFConstructor({
                        orientation: 'portrait',
                        unit: 'mm',
                        format: 'a4'
                    });

                    let renderedHeightPx = 0;
                    let pageIndex = 0;
                    while (renderedHeightPx < canvas.height) {
                        const sliceHeightPx = Math.min(maxContentHeightPx, canvas.height - renderedHeightPx);
                        const pageCanvas = document.createElement('canvas');
                        pageCanvas.width = canvas.width;
                        pageCanvas.height = sliceHeightPx;
                        const pageCtx = pageCanvas.getContext('2d');
                        pageCtx.fillStyle = '#ffffff';
                        pageCtx.fillRect(0, 0, pageCanvas.width, pageCanvas.height);
                        pageCtx.drawImage(canvas, 0, renderedHeightPx, canvas.width, sliceHeightPx, 0, 0, canvas.width, sliceHeightPx);
                        const imgData = pageCanvas.toDataURL('image/jpeg', 0.92);
                        const sliceHeightMm = sliceHeightPx / pxPerMm;
                        if (pageIndex > 0) pdf.addPage();
                        pdf.addImage(imgData, 'JPEG', margin, margin, contentWidth, sliceHeightMm);
                        renderedHeightPx += sliceHeightPx;
                        pageIndex++;
                    }

                    const totalPages = pdf.getNumberOfPages();
                    for (let i = 1; i <= totalPages; i++) {
                        pdf.setPage(i);
                        pdf.setFontSize(9);
                        pdf.setTextColor(150);
                        pdf.text(`Halaman ${i} dari ${totalPages}`, pageWidth / 2, pageHeight - 6, {
                            align: 'center'
                        });
                    }

                    const filename = 'Summary_Report_Detail_' + new Date().toISOString().slice(0, 10) + '.pdf';
                    pdf.save(filename);

                    btn.innerHTML = '<i class="fas fa-check me-1"></i>PDF Downloaded';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-download me-1"></i>Download PDF';
                        btn.disabled = false;
                    }, 2000);
                } catch (err) {
                    console.error('PDF Error:', err);
                    alert(err?.message ? `Gagal generate PDF: ${err.message}` : 'Gagal generate PDF.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-download me-1"></i>Download PDF';
                } finally {
                    document.body.classList.remove('pdf-generating');
                    if (captureHost && captureHost.parentNode) captureHost.parentNode.removeChild(captureHost);
                    if (btn.disabled) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-download me-1"></i>Download PDF';
                    }
                }
            }, 300);
        });

        // Download PDF functionality
        document.getElementById('btnDownloadPDF').addEventListener('click', function() {
            const btn = this;
            const element = document.getElementById('reportContent');
            const notesTextarea = document.getElementById('reportNotes');

            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
            btn.disabled = true;

            // Read notes (don't change UI while generating)
            const notesValue = notesTextarea.value.trim();

            // Capture the full analytics dashboard (not the simplified table template)
            const reportRoot = document.getElementById('reportContent');

            // Wait for charts to fully render
            setTimeout(async () => {
                let captureHost = null;
                try {
                    const jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
                    if (typeof html2canvas === 'undefined' || !jsPDFConstructor) {
                        throw new Error('Library PDF belum siap (html2canvas/jsPDF). Coba refresh (Ctrl+F5).');
                    }

                    // Create hidden (but renderable) clone for capture to avoid UI reflow
                    captureHost = document.createElement('div');
                    captureHost.style.cssText = [
                        'position: fixed',
                        'left: -9999px',
                        'top: 0',
                        'width: 210mm',
                        'background: #ffffff',
                        'pointer-events: none',
                        'z-index: -1',
                        'padding: 0',
                        'margin: 0'
                    ].join(';');

                    if (!reportRoot) {
                        throw new Error('Konten report tidak ditemukan.');
                    }

                    // Clone full report content (dashboard)
                    const clone = reportRoot.cloneNode(true);
                    clone.style.display = 'block';
                    clone.style.width = '210mm';
                    clone.style.maxWidth = '210mm';
                    clone.style.background = '#ffffff';
                    clone.style.padding = '0';
                    clone.style.margin = '0';
                    clone.style.overflow = 'visible';
                    clone.style.maxHeight = 'none';

                    // Remove simplified table template from the captured output
                    const simplified = clone.querySelector('#reportPrint');
                    if (simplified && simplified.parentNode) simplified.parentNode.removeChild(simplified);

                    // Render notes as plain text in captured output
                    const cloneNotesTextarea = clone.querySelector('#reportNotes');
                    const cloneNotesDisplay = clone.querySelector('#reportNotesDisplay');
                    if (cloneNotesTextarea) cloneNotesTextarea.style.display = 'none';
                    if (cloneNotesDisplay) {
                        cloneNotesDisplay.style.display = 'block';
                        cloneNotesDisplay.textContent = notesValue || 'Tidak ada catatan.';
                    }

                    // Re-paint Chart.js canvases into the cloned canvases
                    const originalCanvases = reportRoot.querySelectorAll('canvas');
                    const clonedCanvases = clone.querySelectorAll('canvas');
                    originalCanvases.forEach((originalCanvas, idx) => {
                        const clonedCanvas = clonedCanvases[idx];
                        if (!clonedCanvas) return;
                        try {
                            clonedCanvas.width = originalCanvas.width;
                            clonedCanvas.height = originalCanvas.height;
                            const ctx = clonedCanvas.getContext('2d');
                            ctx.drawImage(originalCanvas, 0, 0);
                        } catch (e) {
                            // ignore per-canvas errors
                        }
                    });

                    captureHost.appendChild(clone);
                    document.body.appendChild(captureHost);

                    document.body.classList.add('pdf-generating');

                    // Prevent Section 3 header from being cut by fixed canvas slicing
                    // If Section 3 starts too close to the bottom of a PDF page, push it to the next page.
                    try {
                        const section3Header = Array.from(clone.querySelectorAll('.section-header h6'))
                            .find(h => (h.textContent || '').trim().startsWith('3.') && (h.textContent || '').toUpperCase().includes('PROGRESS'));
                        const section3 = section3Header ? section3Header.closest('.section-header') : null;
                        if (section3) {
                            // Match the PDF slicing math used below (contentWidth/contentHeight in mm)
                            const pageWidthMm = 210;
                            const pageHeightMm = 297;
                            const marginMm = 10;
                            const contentWidthMm = pageWidthMm - marginMm * 2; // 190
                            const contentHeightMm = pageHeightMm - marginMm * 2; // 277
                            const minSpaceBeforeSectionMm = 35; // keep enough room so header + some content won't be split

                            // Layout must be measurable in DOM
                            const cloneRect = clone.getBoundingClientRect();
                            const sectionRect = section3.getBoundingClientRect();

                            const pxPerMmForSlice = cloneRect.width / contentWidthMm;
                            const pageHeightPx = contentHeightMm * pxPerMmForSlice;
                            const offsetY = sectionRect.top - cloneRect.top;
                            const withinPageY = ((offsetY % pageHeightPx) + pageHeightPx) % pageHeightPx;
                            const spaceLeftPx = pageHeightPx - withinPageY;
                            const minSpacePx = minSpaceBeforeSectionMm * pxPerMmForSlice;

                            // Only push when we're too close to the bottom of the page.
                            if (spaceLeftPx > 0 && spaceLeftPx < minSpacePx) {
                                const spacer = document.createElement('div');
                                spacer.style.height = `${spaceLeftPx}px`;
                                spacer.style.width = '100%';
                                spacer.style.background = '#ffffff';
                                section3.parentNode.insertBefore(spacer, section3);
                            }
                        }
                    } catch (e) {
                        // ignore layout/page-break helper errors
                    }

                    const canvas = await html2canvas(clone, {
                        scale: 2,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        logging: false,
                        scrollX: 0,
                        scrollY: -window.scrollY,
                        windowWidth: clone.scrollWidth,
                        windowHeight: clone.scrollHeight
                    });
                    const pdf = new jsPDFConstructor('p', 'mm', 'a4');

                    const pageWidth = 210;
                    const pageHeight = 297;
                    const margin = 10;
                    const contentWidth = pageWidth - margin * 2;
                    const contentHeight = pageHeight - margin * 2;

                    // Slice the canvas into page-sized images to avoid overlap/duplicate cuts
                    const pxPerMm = canvas.width / contentWidth;
                    const pageHeightPx = Math.floor(contentHeight * pxPerMm);

                    let renderedHeightPx = 0;
                    let pageIndex = 0;

                    while (renderedHeightPx < canvas.height) {
                        const sliceHeightPx = Math.min(pageHeightPx, canvas.height - renderedHeightPx);

                        const pageCanvas = document.createElement('canvas');
                        pageCanvas.width = canvas.width;
                        pageCanvas.height = sliceHeightPx;

                        const pageCtx = pageCanvas.getContext('2d');
                        pageCtx.fillStyle = '#ffffff';
                        pageCtx.fillRect(0, 0, pageCanvas.width, pageCanvas.height);
                        pageCtx.drawImage(
                            canvas,
                            0,
                            renderedHeightPx,
                            canvas.width,
                            sliceHeightPx,
                            0,
                            0,
                            canvas.width,
                            sliceHeightPx
                        );

                        const imgData = pageCanvas.toDataURL('image/jpeg', 0.92);
                        const sliceHeightMm = sliceHeightPx / pxPerMm;

                        if (pageIndex > 0) pdf.addPage();
                        pdf.addImage(imgData, 'JPEG', margin, margin, contentWidth, sliceHeightMm);

                        renderedHeightPx += sliceHeightPx;
                        pageIndex++;
                    }

                    const totalPages = pdf.getNumberOfPages();
                    for (let i = 1; i <= totalPages; i++) {
                        pdf.setPage(i);
                        pdf.setFontSize(9);
                        pdf.setTextColor(150);
                        pdf.text(
                            `Halaman ${i} dari ${totalPages}`,
                            pageWidth / 2,
                            pageHeight - 6, {
                                align: 'center'
                            }
                        );
                    }

                    const filename = 'Summary_Report_Evatek_' + new Date().toISOString().slice(0, 10) + '.pdf';
                    pdf.save(filename);

                    btn.innerHTML = '<i class="fas fa-check me-1"></i>PDF Downloaded';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-download me-1"></i>Download PDF';
                        btn.disabled = false;
                    }, 2000);
                } catch (err) {
                    console.error('PDF Error:', err);
                    alert(err?.message ? `Gagal generate PDF: ${err.message}` : 'Gagal generate PDF.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-download me-1"></i>Download PDF';
                } finally {
                    document.body.classList.remove('pdf-generating');

                    if (captureHost && captureHost.parentNode) {
                        captureHost.parentNode.removeChild(captureHost);
                    }

                    // Restore button state (success path already schedules UI reset)
                    if (btn.disabled) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-download me-1"></i>Download PDF';
                    }
                }
            }, 700); // give Chart.js time to paint
        });

        // Print PDF functionality (alternative method)
        document.getElementById('btnPrintPDF').addEventListener('click', function() {
            const notesTextarea = document.getElementById('reportNotes');
            const notesValue = notesTextarea.value.trim();

            // Update values used by the simplified print template.
            // Do NOT toggle UI visibility here; @media print will show #reportPrint only during printing.
            const printNotes = document.getElementById('printNotes');
            const printReportDate = document.getElementById('printReportDate');
            if (printNotes) {
                printNotes.textContent = notesValue || 'Tidak ada catatan.';
            }
            if (printReportDate) {
                printReportDate.textContent = new Date().toLocaleString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            window.print();
        });

        function generateSummaryReport() {
            const rows = document.querySelectorAll('#items-tbody tr.evatek-row');

            // Initialize counters
            let totalItems = 0;
            let statusCount = {
                'approve': 0,
                'on_progress': 0,
                'not_approve': 0
            };
            let picCount = {};
            let vendorCount = {};

            rows.forEach(row => {
                totalItems++;

                // Status
                const status = row.getAttribute('data-status');
                if (status === 'approve') {
                    statusCount.approve++;
                } else if (status === 'not_approve') {
                    statusCount.not_approve++;
                } else {
                    statusCount.on_progress++;
                }

                // PIC
                const pic = row.getAttribute('data-pic') || '-';
                if (pic && pic !== '-') {
                    picCount[pic] = (picCount[pic] || 0) + 1;
                }

                // Vendor (from second column)
                const vendorCell = row.querySelectorAll('td')[1];
                const vendorName = vendorCell ? vendorCell.textContent.trim() : '-';
                if (vendorName && vendorName !== '-') {
                    vendorCount[vendorName] = (vendorCount[vendorName] || 0) + 1;
                }
            });

            // Update Modal Content
            document.getElementById('summaryTotalItems').textContent = totalItems;
            document.getElementById('summaryApproved').textContent = statusCount.approve;
            document.getElementById('summaryOnProgress').textContent = statusCount.on_progress;
            document.getElementById('summaryNotApproved').textContent = statusCount.not_approve;

            // Update Simplified Print/PDF Content
            const printTotal = document.getElementById('printTotalItems');
            const printApproved = document.getElementById('printApproved');
            const printOnProgress = document.getElementById('printOnProgress');
            const printNotApproved = document.getElementById('printNotApproved');
            if (printTotal) printTotal.textContent = totalItems;
            if (printApproved) printApproved.textContent = statusCount.approve;
            if (printOnProgress) printOnProgress.textContent = statusCount.on_progress;
            if (printNotApproved) printNotApproved.textContent = statusCount.not_approve;

            const printPicRows = document.getElementById('printPicRows');
            const printVendorRows = document.getElementById('printVendorRows');
            const sortedPicsForPrint = Object.entries(picCount).sort((a, b) => b[1] - a[1]);
            const sortedVendorsForPrint = Object.entries(vendorCount).sort((a, b) => b[1] - a[1]);

            if (printPicRows) {
                if (sortedPicsForPrint.length === 0) {
                    printPicRows.innerHTML = '<tr><td style="border: 1px solid #333; padding: 6px;" colspan="2">Tidak ada data</td></tr>';
                } else {
                    printPicRows.innerHTML = sortedPicsForPrint.map(([pic, count]) => {
                        return `<tr>
                            <td style="border: 1px solid #333; padding: 6px;">${pic}</td>
                            <td style="border: 1px solid #333; padding: 6px; text-align:right;">${count}</td>
                        </tr>`;
                    }).join('');
                }
            }

            if (printVendorRows) {
                if (sortedVendorsForPrint.length === 0) {
                    printVendorRows.innerHTML = '<tr><td style="border: 1px solid #333; padding: 6px;" colspan="2">Tidak ada data</td></tr>';
                } else {
                    printVendorRows.innerHTML = sortedVendorsForPrint.map(([vendor, count]) => {
                        return `<tr>
                            <td style="border: 1px solid #333; padding: 6px;">${vendor}</td>
                            <td style="border: 1px solid #333; padding: 6px; text-align:right;">${count}</td>
                        </tr>`;
                    }).join('');
                }
            }

            // Update Progress Bar
            const approvedPct = totalItems > 0 ? ((statusCount.approve / totalItems) * 100).toFixed(1) : 0;
            const onProgressPct = totalItems > 0 ? ((statusCount.on_progress / totalItems) * 100).toFixed(1) : 0;
            const notApprovedPct = totalItems > 0 ? ((statusCount.not_approve / totalItems) * 100).toFixed(1) : 0;

            // Update Simplified Print/PDF Percentage Content
            const printApprovedPctEl = document.getElementById('printApprovedPct');
            const printOnProgressPctEl = document.getElementById('printOnProgressPct');
            const printNotApprovedPctEl = document.getElementById('printNotApprovedPct');
            const printApprovalRateEl = document.getElementById('printApprovalRate');
            if (printApprovedPctEl) printApprovedPctEl.textContent = approvedPct + '%';
            if (printOnProgressPctEl) printOnProgressPctEl.textContent = onProgressPct + '%';
            if (printNotApprovedPctEl) printNotApprovedPctEl.textContent = notApprovedPct + '%';
            if (printApprovalRateEl) printApprovalRateEl.textContent = approvedPct + '%';

            document.getElementById('progressApproved').style.width = approvedPct + '%';
            document.getElementById('progressApproved').textContent = approvedPct > 10 ? approvedPct + '%' : '';
            document.getElementById('progressOnProgress').style.width = onProgressPct + '%';
            document.getElementById('progressNotApproved').style.width = notApprovedPct + '%';

            document.getElementById('progressApprovedPct').textContent = approvedPct + '%';
            document.getElementById('progressOnProgressPct').textContent = onProgressPct + '%';
            document.getElementById('progressNotApprovedPct').textContent = notApprovedPct + '%';

            // Report Date
            document.getElementById('reportDate').textContent = new Date().toLocaleString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Generate Automatic Interpretation
            generateInterpretation(totalItems, statusCount, approvedPct, onProgressPct, notApprovedPct);

            // Generate Chart Numeric Summary
            generateChartSummary(statusCount, picCount);

            // Generate Progress Analysis
            generateProgressAnalysis(approvedPct, onProgressPct, notApprovedPct, totalItems, statusCount);

            // Generate Distribution Summary
            generateDistributionSummary(picCount, vendorCount);




            // Status Pie Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            if (statusChartInstance) statusChartInstance.destroy();
            statusChartInstance = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'On Progress', 'Not Approved'],
                    datasets: [{
                        data: [statusCount.approve, statusCount.on_progress, statusCount.not_approve],
                        backgroundColor: ['#28AC00', '#FF9500', '#BD0000'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });

            // PIC Bar Chart
            const picCtx = document.getElementById('picChart').getContext('2d');
            if (picChartInstance) picChartInstance.destroy();
            const sortedPics = Object.entries(picCount).sort((a, b) => b[1] - a[1]);
            const picLabels = sortedPics.map(p => p[0]);
            const picData = sortedPics.map(p => p[1]);
            const picColors = ['#667eea', '#764ba2', '#1976D2', '#7B1FA2', '#00796B', '#F57C00'];

            picChartInstance = new Chart(picCtx, {
                type: 'bar',
                data: {
                    labels: picLabels,
                    datasets: [{
                        label: 'Items',
                        data: picData,
                        backgroundColor: picColors.slice(0, picLabels.length),
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // By PIC Badges
            const picContainer = document.getElementById('summaryByPic');
            picContainer.innerHTML = '';
            const badgeColors = ['#1976D2', '#7B1FA2', '#00796B', '#F57C00', '#C62828'];
            let colorIndex = 0;
            for (const [pic, count] of sortedPics) {
                const badge = document.createElement('div');
                badge.style.cssText = `
                    background: ${badgeColors[colorIndex % badgeColors.length]};
                    color: white;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 13px;
                    font-weight: 600;
                `;
                badge.innerHTML = `${pic}: <strong>${count}</strong>`;
                picContainer.appendChild(badge);
                colorIndex++;
            }

            if (sortedPics.length === 0) {
                picContainer.innerHTML = '<span class="text-muted">Tidak ada data PIC</span>';
            }

            // By Vendor
            const vendorContainer = document.getElementById('summaryByVendor');
            vendorContainer.innerHTML = '';
            const sortedVendors = Object.entries(vendorCount).sort((a, b) => b[1] - a[1]);

            for (const [vendor, count] of sortedVendors) {
                const vendorRow = document.createElement('div');
                vendorRow.style.cssText = `
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 12px;
                    border-bottom: 1px solid #eee;
                    font-size: 14px;
                `;
                vendorRow.innerHTML = `
                    <span style="color: #333;">${vendor}</span>
                    <span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 15px; font-weight: 600; font-size: 12px;">${count} item${count > 1 ? 's' : ''}</span>
                `;
                vendorContainer.appendChild(vendorRow);
            }

            if (sortedVendors.length === 0) {
                vendorContainer.innerHTML = '<span class="text-muted">Tidak ada data Vendor</span>';
            }
        }

        // Generate automatic interpretation based on data
        function generateInterpretation(totalItems, statusCount, approvedPct, onProgressPct, notApprovedPct) {
            const interpretationEl = document.getElementById('interpretationText');
            let interpretation = '';

            if (totalItems === 0) {
                interpretation = 'Belum ada data item Evatek yang tercatat dalam sistem.';
            } else {
                interpretation = `Dari total <strong>${totalItems} item</strong> Evatek yang tercatat: `;

                // Approval rate analysis
                if (parseFloat(approvedPct) >= 70) {
                    interpretation += `Tingkat approval sangat baik (<strong class="text-success">${approvedPct}%</strong> approved). `;
                } else if (parseFloat(approvedPct) >= 50) {
                    interpretation += `Tingkat approval cukup baik (<strong class="text-warning">${approvedPct}%</strong> approved). `;
                } else if (parseFloat(approvedPct) > 0) {
                    interpretation += `Tingkat approval masih rendah (<strong class="text-danger">${approvedPct}%</strong> approved). `;
                } else {
                    interpretation += `Belum ada item yang disetujui. `;
                }

                // Progress analysis
                if (parseFloat(onProgressPct) > 50) {
                    interpretation += `Sebagian besar item (<strong>${onProgressPct}%</strong>) masih dalam proses review. `;
                } else if (parseFloat(onProgressPct) > 0) {
                    interpretation += `Terdapat <strong>${onProgressPct}%</strong> item yang sedang dalam proses review. `;
                }

                // Rejection analysis
                if (parseFloat(notApprovedPct) > 30) {
                    interpretation += `<span class="text-danger"><strong>Perhatian:</strong> Tingkat penolakan cukup tinggi (${notApprovedPct}%), memerlukan tindak lanjut segera.</span>`;
                } else if (parseFloat(notApprovedPct) > 0) {
                    interpretation += `Terdapat ${notApprovedPct}% item yang tidak disetujui dan memerlukan revisi.`;
                } else {
                    interpretation += `<span class="text-success">Tidak ada item yang ditolak.</span>`;
                }
            }

            interpretationEl.innerHTML = interpretation;
        }

        // Generate chart numeric summary
        function generateChartSummary(statusCount, picCount) {
            const summaryEl = document.getElementById('chartNumericSummary');
            const totalPics = Object.keys(picCount).length;
            const picEntries = Object.entries(picCount).sort((a, b) => b[1] - a[1]);

            let summary = '<div class="row">';
            summary += '<div class="col-md-6">';
            summary += '<strong>Status Distribution:</strong><br>';
            summary += `• Approved: <span class="badge bg-success">${statusCount.approve} items</span><br>`;
            summary += `• On Progress: <span class="badge bg-warning">${statusCount.on_progress} items</span><br>`;
            summary += `• Not Approved: <span class="badge bg-danger">${statusCount.not_approve} items</span>`;
            summary += '</div>';

            summary += '<div class="col-md-6">';
            summary += `<strong>Distribusi PIC (${totalPics} PIC aktif):</strong><br>`;
            if (picEntries.length > 0) {
                const topPic = picEntries[0];
                summary += `• PIC dengan item terbanyak: <strong>${topPic[0]}</strong> (${topPic[1]} items)<br>`;

                if (picEntries.length > 1) {
                    const avgItems = picEntries.reduce((sum, p) => sum + p[1], 0) / picEntries.length;
                    summary += `• Rata-rata item per PIC: <strong>${avgItems.toFixed(1)}</strong> items`;
                }
            } else {
                summary += 'Tidak ada data PIC';
            }
            summary += '</div>';
            summary += '</div>';

            summaryEl.innerHTML = summary;
        }

        // Generate progress analysis
        function generateProgressAnalysis(approvedPct, onProgressPct, notApprovedPct, totalItems, statusCount) {
            const analysisEl = document.getElementById('progressAnalysis');
            let analysis = '';

            const completionRate = parseFloat(approvedPct);
            const pendingRate = parseFloat(onProgressPct);
            const rejectionRate = parseFloat(notApprovedPct);

            // Overall status
            if (completionRate >= 80) {
                analysis += '✅ <strong>Status: Sangat Baik</strong> - Mayoritas item telah diselesaikan.<br>';
            } else if (completionRate >= 60) {
                analysis += '✓ <strong>Status: Baik</strong> - Progres penyelesaian berjalan lancar.<br>';
            } else if (completionRate >= 40) {
                analysis += '⚠️ <strong>Status: Cukup</strong> - Masih banyak item yang perlu diselesaikan.<br>';
            } else {
                analysis += '⚠️ <strong>Status: Perlu Perhatian</strong> - Tingkat penyelesaian masih rendah.<br>';
            }

            // Recommendations
            analysis += '<br><strong>Rekomendasi:</strong><br>';

            if (rejectionRate > 20) {
                analysis += `• <span class="text-danger">Prioritas Tinggi:</span> ${statusCount.not_approve} item ditolak memerlukan revisi segera.<br>`;
            }

            if (pendingRate > 40) {
                analysis += `• <span class="text-warning">Perlu Percepatan:</span> ${statusCount.on_progress} item dalam proses review perlu dipercepat.<br>`;
            }

            if (completionRate < 50) {
                analysis += '• Tingkatkan koordinasi dengan vendor dan PIC untuk mempercepat proses approval.<br>';
            }

            if (totalItems < 5) {
                analysis += '• Volume item masih sedikit, pertimbangkan untuk menambah item yang perlu di-review.';
            } else {
                analysis += `• Lanjutkan monitoring ${totalItems} item secara berkala untuk memastikan progress tetap on-track.`;
            }

            analysisEl.innerHTML = analysis;
        }

        // Generate distribution summary
        function generateDistributionSummary(picCount, vendorCount) {
            const summaryEl = document.getElementById('distributionSummary');
            const totalPics = Object.keys(picCount).length;
            const totalVendors = Object.keys(vendorCount).length;

            let summary = '<div class="row">';

            // PIC Summary
            summary += '<div class="col-md-6">';
            summary += `<strong><i class="fas fa-user-tie me-1"></i> Ringkasan PIC:</strong><br>`;
            summary += `• Total PIC aktif: <strong>${totalPics}</strong> divisi<br>`;

            if (totalPics > 0) {
                const picEntries = Object.entries(picCount).sort((a, b) => b[1] - a[1]);
                const maxLoad = picEntries[0][1];
                const minLoad = picEntries[picEntries.length - 1][1];
                const avgLoad = picEntries.reduce((sum, p) => sum + p[1], 0) / picEntries.length;

                summary += `• Beban tertinggi: <strong>${maxLoad}</strong> items (${picEntries[0][0]})<br>`;
                summary += `• Beban terendah: <strong>${minLoad}</strong> items (${picEntries[picEntries.length - 1][0]})<br>`;
                summary += `• Rata-rata beban: <strong>${avgLoad.toFixed(1)}</strong> items per PIC`;

                if (maxLoad > avgLoad * 2) {
                    summary += '<br><span class="text-warning">⚠️ Distribusi tidak merata - pertimbangkan redistribusi beban kerja.</span>';
                }
            }
            summary += '</div>';

            // Vendor Summary
            summary += '<div class="col-md-6">';
            summary += `<strong><i class="fas fa-building me-1"></i> Ringkasan Vendor:</strong><br>`;
            summary += `• Total vendor terlibat: <strong>${totalVendors}</strong> vendor<br>`;

            if (totalVendors > 0) {
                const vendorEntries = Object.entries(vendorCount).sort((a, b) => b[1] - a[1]);
                const topVendor = vendorEntries[0];
                const totalVendorItems = vendorEntries.reduce((sum, v) => sum + v[1], 0);
                const avgVendorItems = totalVendorItems / totalVendors;

                summary += `• Vendor utama: <strong>${topVendor[0]}</strong> (${topVendor[1]} items)<br>`;
                summary += `• Rata-rata item per vendor: <strong>${avgVendorItems.toFixed(1)}</strong> items`;

                if (totalVendors >= 5) {
                    summary += '<br><span class="text-info">ℹ️ Diversifikasi vendor baik untuk risk management.</span>';
                }
            }
            summary += '</div>';

            summary += '</div>';
            summaryEl.innerHTML = summary;
        }

        // Generate detail table for Section 5
        function generateDetailTable(rows) {
            const tableBody = document.getElementById('summaryDetailBody');
            const printBody = document.getElementById('printDetailRows');
            if (!tableBody) return;

            // Step 1: Group rows by item name
            const groupedItems = new Map();
            rows.forEach(row => {
                const itemLink = row.querySelector('a');
                const itemName = itemLink ? itemLink.textContent.replace(/Baru/g, '').trim() : '-';
                if (!groupedItems.has(itemName)) groupedItems.set(itemName, []);
                groupedItems.get(itemName).push(row);
            });

            let modalHtml = '';
            let printHtml = '';
            let no = 0;

            groupedItems.forEach((groupRows, itemName) => {
                no++;
                const rowCount = groupRows.length;

                groupRows.forEach((row, index) => {
                    const cells = row.querySelectorAll('td');
                    const vendorName = cells[1] ? cells[1].textContent.trim() : '-';
                    const pic = row.getAttribute('data-pic') || '-';
                    const targetDate = cells[4] ? cells[4].textContent.trim() : '-';
                    const lastUpdate = cells[9] ? cells[9].textContent.trim() : '-';
                    const statusRaw = row.getAttribute('data-status') || 'on_progress';

                    // Logika Status Pengadaan & Posisi
                    const posisiEl = cells[7] ? cells[7].querySelector('.badge') : null;
                    const posisi = posisiEl ? posisiEl.textContent.trim() : '-';

                    let statusPengadaan = '-';
                    if (statusRaw === 'approve') statusPengadaan = 'Completed';
                    else if (posisi.includes('Vendor')) statusPengadaan = 'Evatek Vendor';
                    else if (posisi.includes('Desain')) statusPengadaan = 'Evatek Desain';
                    else if (statusRaw === 'not_approve') statusPengadaan = 'Not Approved';

                    const statusLabel = statusRaw === 'approve' ? 'Closed' : 'Open';
                    const statusColor = statusRaw === 'approve' ? '#28AC00' : '#FF9500';

                    //ambli log revisi
                    let revisions = [];
                    try {
                        revisions = JSON.parse(row.getAttribute('data-revisions') || '[]');
                    } catch (e) {}

                    let logText = '';
                    let logTextPrint = '';

                    // HANYA AMBIL R TERAKHIR
                    if (revisions.length > 0) {
                        const latestRev = revisions[revisions.length - 1]; // Mengambil data paling baru
                        const code = latestRev.code || 'R' + (revisions.length - 1);
                        const log = (latestRev.log && latestRev.log !== '-') ? latestRev.log : 'No log entry';
                        const date = latestRev.date ? `<small class="text-muted">[${latestRev.date}]</small>` : '';

                        logText = `<div style="line-height: 1.4;">
                <span class="badge bg-primary" style="font-size: 10px;">${code}</span> 
                ${date} <span>${log}</span>
               </div>`;

                        logTextPrint = `[${code}] ${log}`;
                    } else {
                        const mainLog = row.getAttribute('data-log') || 'Belum ada catatan';
                        logText = `<div class="text-muted italic">${mainLog}</div>`;
                        logTextPrint = mainLog;
                    }

                    // --- RENDER MODAL ROW ---
                    const isLastInGroup = (index === rowCount - 1);
                    const cellBorderBottom = isLastInGroup ? 'border-bottom: 2px solid #333;' : 'border-bottom: 1px solid #ccc;';
                    modalHtml += `<tr>`;
                    if (index === 0) {
                        modalHtml += `<td rowspan="${rowCount}" style="text-align: center; font-weight: bold; vertical-align: middle; border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; border-bottom: 2px solid #333; background: #fdfdfd;">${no}</td>`;
                        modalHtml += `<td rowspan="${rowCount}" style="font-weight: 600; color: #1a5276; vertical-align: middle; border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; border-bottom: 2px solid #333; background: #fdfdfd;">${itemName}</td>`;
                    }
                    modalHtml += `<td style="border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; ${cellBorderBottom} padding: 12px 10px; font-size: 11px;">
                <div style="font-weight: 700; color: #444; margin-bottom: 5px;">
                    <i class="fas fa-building me-1"></i>${vendorName}
                </div>
                ${logText}
              </td>`;
                    modalHtml += `<td style="border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; ${cellBorderBottom} text-align: center; font-size: 11px; vertical-align: middle;">${lastUpdate}</td>`;
                    modalHtml += `<td style="border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; ${cellBorderBottom} text-align: center; vertical-align: middle;"><span class="badge" style="background: #e3f2fd; color: #1565C0; font-size: 9px;">${statusPengadaan}</span></td>`;
                    modalHtml += `<td style="border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; ${cellBorderBottom} text-align: center; font-weight: 600; vertical-align: middle;">${pic}</td>`;
                    modalHtml += `<td style="border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; ${cellBorderBottom} text-align: center; font-size: 11px; vertical-align: middle;">${targetDate}</td>`;
                    modalHtml += `<td style="border-left: 1px solid #bbb; border-right: 1px solid #bbb; border-top: 1px solid #bbb; ${cellBorderBottom} text-align: center; vertical-align: middle;"><span style="color: ${statusColor}; font-weight: bold; font-size: 10px;">${statusLabel}</span></td>`;
                    modalHtml += `</tr>`;

                    // --- RENDER PRINT ROW (Sama seperti modal, hanya R terakhir) ---
                    const printCellBorder = isLastInGroup ? 'border-bottom: 2px solid #333;' : 'border-bottom: 1px solid #aaa;';
                    printHtml += `<tr>`;
                    if (index === 0) {
                        printHtml += `<td rowspan="${rowCount}" style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; border-bottom: 2px solid #333; text-align: center; vertical-align: middle;">${no}</td>`;
                        printHtml += `<td rowspan="${rowCount}" style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; border-bottom: 2px solid #333; vertical-align: middle;">${itemName}</td>`;
                    }
                    printHtml += `<td style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; ${printCellBorder} font-size: 7px; padding: 3px;"><strong>${vendorName}:</strong> ${logTextPrint}</td>`;
                    printHtml += `<td style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; ${printCellBorder} text-align: center;">${lastUpdate}</td>`;
                    printHtml += `<td style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; ${printCellBorder} text-align: center;">${statusPengadaan}</td>`;
                    printHtml += `<td style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; ${printCellBorder} text-align: center;">${pic}</td>`;
                    printHtml += `<td style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; ${printCellBorder} text-align: center;">${targetDate}</td>`;
                    printHtml += `<td style="border-left: 1px solid #333; border-right: 1px solid #333; border-top: 1px solid #333; ${printCellBorder} text-align: center;">${statusLabel}</td>`;
                    printHtml += `</tr>`;
                });
            });

            tableBody.innerHTML = modalHtml || '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
            if (printBody) printBody.innerHTML = printHtml || '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
        }
    });
</script>
@endpush