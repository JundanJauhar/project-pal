@extends('layouts.app')

@section('title', 'Detail Project - ' . $project->name_project)

@section('content')
<style>
    .procurement-header {
        padding: 25px;
        background: white;
    }

    .timeline-container {
        display: flex;
        justify-content: space-between;
        margin: 40px 0;
        position: relative;
    }

    .timeline-container::before {
        content: "";
        position: absolute;
        top: 24px;
        left: 0;
        width: 100%;
        height: 3px;
        background: #c7e5c6;
        z-index: 1;
    }

    .timeline-step {
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .timeline-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #c7e5c6;
        color: green;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20px;
        margin: auto;
    }

    .timeline-step.active .timeline-icon {
        background: #ECAD02;
        color: white;
        font-weight: bold;
    }

    .timeline-step.completed .timeline-icon {
        background: #28AC00;
        color: white;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 15px;
        margin-top: 0;
    }

    .doc-card {
        background: #F7F7F7;
        border-radius: 10px;
        padding: 15px 18px;
        margin-bottom: 20px;
    }

    /* Review Document Styles */
    .review-document-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: px;
        margin-bottom: 30px;
    }

    .review-left {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }

    .review-left h6 {
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .review-notes-textarea {
        width: 100%;
        min-height: 180px;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        line-height: 1.6;
        color: #000;
    }

    .review-notes-textarea::placeholder {
        color: #999;
    }

    .review-notes-textarea:focus {
        outline: none;
        border-color: #003d82;
        box-shadow: 0 0 0 3px rgba(0, 61, 130, 0.1);
    }

    .review-right {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }

    .review-right h6 {
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .file-upload-wrapper {
        border: 2px dashed #d0d0d0;
        border-radius: 8px;
        padding: 30px 20px;
        text-align: center;
        background: #fafafa;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 15px;
    }

    .file-upload-wrapper:hover {
        border-color: #003d82;
        background: #f0f5ff;
    }

    .file-upload-wrapper i {
        font-size: 32px;
        color: #003d82;
        margin-bottom: 10px;
    }

    .file-upload-wrapper p {
        font-size: 12px;
        color: #666;
        margin: 5px 0 0 0;
    }

    .file-input-hidden {
        display: none;
    }

    .file-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .file-list li {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 8px;
        font-size: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-list .remove-file {
        color: #dc3545;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-kirim {
        background: #003d82 !important;
        border-color: #003d82 !important;
        color: white !important;
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 6px;
        width: 100%;
        margin-top: 15px;
    }

    .btn-kirim:hover {
        background: #002851 !important;
        border-color: #002851 !important;
    }

    @media (max-width: 768px) {
        .review-document-section {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    .bi-x-circle {
        font-size: 24px;
        color: #dc3545;
        margin-bottom: 30px;
    }
    /* Posisikan logo ke tengah */
.header-logo-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-top: -80px;
    margin-bottom: 15px;
    background-color: white;
}

.logo {
    height: 220px;
    object-fit: contain;
    margin-top: 10px;
}

/* Tanda X ke kiri mengikuti margin konten */
.close-btn {
    position: absolute;
    right: 90px; /* Geser ke kiri sesuai kebutuhan */
    top: 110px;  /* Sejajarkan dengan konten berikutnya */
    font-size: 28px;
    margin-top: -40px;
    color: #DA3B3B;
    cursor: pointer;
}

.close-btn:hover {
    opacity: 0.7;
}
</style>
<div class="header-logo-wrapper">
    <img src="{{ asset('images/logo-pal.png') }}" alt="Logo PAL" class="logo">
</div>
<a href="javascript:history.back()" class="close-btn">
    <i class="bi bi-x-circle"></i>
</a>
<div class="procurement-header">
    {{-- Header Project --}}
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4>Daftar Pengadaan</h4>
            <p><strong>Nama Project:</strong> {{ $project->code_project }}</p>
            <p><strong>Vendor:</strong> {{ $project->contracts->first()->vendor->name_vendor ?? '-' }}</p>
            <p><strong>Deskripsi:</strong> {{ $project->description }}</p>
        </div>
        <span></span>
        <div class="text-end">
            <p><strong>Prioritas:</strong> {{ strtoupper($project->priority) }}</p>
            <p><strong>Tanggal Dibuat:</strong> {{ $project->created_at->format('d/m/Y') }}</p>
            <p><strong>Tanggal Target:</strong> {{ $project->end_date->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- Detail Pengadaan --}}
    <h5 class="section-title">Detail Pengadaan</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Pengadaan</th>
                <th>Spesifikasi</th>
                <th>Jumlah</th>
                <th>Harga Estimasi</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->requestProcurements as $req)
                @foreach ($req->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->specification }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                    <td>Rp {{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->estimated_price * $item->quantity, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>


    {{-- REVIEW DOCUMENT --}}
    <h5 class="section-title">Review Document</h5>
    <div class="review-document-section">
        {{-- Kolom Kiri: Review Notes --}}
        <div class="review-left">
            <h6>Catatan Review</h6>
            <textarea id="reviewNotesTextarea" class="review-notes-textarea" placeholder="Masukkan catatan review pengadaan di sini...">{{ $project->review_notes ?? '' }}</textarea>
        </div>

        {{-- Kolom Kanan: Upload Document --}}
        <div class="review-right">
            <h6>Dokumen Pengjuan</h6>
            <div class="file-upload-wrapper" id="fileUploadArea" onclick="document.getElementById('fileInput').click();">
                <div>
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p><strong>Choose File</strong></p>
                    <p>PDF, DOC, DOCX, Maks. 10 MB</p>
                </div>
            </div>
            <ul class="file-list" id="fileList"></ul>
            <button type="button" class="btn btn-primary btn-kirim" id="submitReview">KIRIM</button>
        </div>
    </div>
</div>

<script>
    const fileList = document.getElementById('fileList');
    const reviewNotesTextarea = document.getElementById('reviewNotesTextarea');
    let selectedFiles = [];
    let autoSaveTimeout;

    // Create hidden file input
    const hiddenFileInput = document.createElement('input');
    hiddenFileInput.type = 'file';
    hiddenFileInput.id = 'fileInput';
    hiddenFileInput.className = 'file-input-hidden';
    hiddenFileInput.accept = '.pdf,.doc,.docx';
    hiddenFileInput.setAttribute('data-max-size', 10 * 1024 * 1024); // 10 MB
    hiddenFileInput.multiple = true;
    document.body.appendChild(hiddenFileInput);

    // Auto-save review notes
    reviewNotesTextarea.addEventListener('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            saveReviewNotes();
        }, 1500); // Save after 1.5 seconds of inactivity
    });

    // File input change event
    hiddenFileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            const maxSize = parseInt(hiddenFileInput.getAttribute('data-max-size'));
            if (file.size > maxSize) {
                alert(`File ${file.name} terlalu besar. Maksimal 10 MB.`);
                return;
            }
            selectedFiles.push(file);
        });
        renderFileList();
    });

    // Render file list
    function renderFileList() {
        fileList.innerHTML = selectedFiles.map((file, index) => `
            <li>
                <span><i class="bi bi-file-earmark"></i> ${file.name}</span>
                <span class="remove-file" onclick="removeFile(${index})">×</span>
            </li>
        `).join('');
    }

    // Remove file
    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        renderFileList();
    };

    // Save review notes
    window.saveReviewNotes = function() {
        const reviewNotes = reviewNotesTextarea.value.trim();

        if (!reviewNotes) {
            return;
        }

        fetch('{{ route("projects.saveReviewNotes") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                project_id: '{{ $project->project_id }}',
                review_notes: reviewNotes
            })
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Catatan review berhasil disimpan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    };

    // Submit review documents
    document.getElementById('submitReview').addEventListener('click', function() {
        if (selectedFiles.length === 0) {
            alert('Silakan pilih file terlebih dahulu');
            return;
        }

        const formData = new FormData();
        formData.append('project_id', '{{ $project->project_id }}');
        selectedFiles.forEach(file => {
            formData.append('documents[]', file);
        });

        fetch('{{ route("projects.uploadReview") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Dokumen review berhasil dikirim!');
                selectedFiles = [];
                renderFileList();
                hiddenFileInput.value = '';
            } else {
                alert('Error: ' + (data.message || 'Gagal mengirim dokumen'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengirim dokumen');
        });
    });
</script>

@endsection